<?php

namespace App\Services;

/**
 * Service untuk komunikasi langsung dengan RFID Reader HW-VX6330K v2
 * via TCP/IP socket menggunakan protokol binary UHFReader18
 */
class RfidReaderService
{
    private string $ip;
    private int $port;
    private int $timeout;

    public function __construct()
    {
        $this->ip = env('RFID_READER_IP', '192.168.1.190');
        $this->port = (int) env('RFID_READER_PORT', 6000);
        $this->timeout = (int) env('RFID_READER_TIMEOUT', 3);
    }

    /**
     * CRC-16/MCRF4XX checksum calculation
     */
    private function calculateChecksum(string $data): string
    {
        $value = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $value ^= ord($data[$i]);
            for ($j = 0; $j < 8; $j++) {
                if ($value & 0x0001) {
                    $value = ($value >> 1) ^ 0x8408;
                } else {
                    $value = $value >> 1;
                }
            }
        }
        $crcLsb = $value & 0xFF;
        $crcMsb = ($value >> 8) & 0xFF;
        return chr($crcLsb) . chr($crcMsb);
    }

    /**
     * Build command frame
     */
    private function buildCommand(int $cmd, array $data = []): string
    {
        $address = 0xFF;
        $frameLen = 4 + count($data);

        $frame = chr($frameLen) . chr($address) . chr($cmd);
        foreach ($data as $byte) {
            $frame .= chr($byte);
        }
        $frame .= $this->calculateChecksum($frame);
        return $frame;
    }

    /**
     * Send command and receive response via TCP socket
     */
    private function sendCommand(int $cmd, array $data = []): ?array
    {
        $frame = $this->buildCommand($cmd, $data);

        $socket = @fsockopen($this->ip, $this->port, $errno, $errstr, $this->timeout);
        if (!$socket) {
            return ['error' => "Tidak bisa terhubung ke reader: $errstr ($errno)"];
        }

        stream_set_timeout($socket, $this->timeout);

        // Send command
        fwrite($socket, $frame);

        // Read response length byte
        $lenByte = fread($socket, 1);
        if ($lenByte === false || strlen($lenByte) === 0) {
            fclose($socket);
            return ['error' => 'Reader tidak merespons (timeout)'];
        }

        $frameLength = ord($lenByte);
        $remaining = '';
        $bytesNeeded = $frameLength;

        while (strlen($remaining) < $bytesNeeded) {
            $chunk = fread($socket, $bytesNeeded - strlen($remaining));
            if ($chunk === false || strlen($chunk) === 0) {
                break;
            }
            $remaining .= $chunk;
        }

        fclose($socket);

        $response = $lenByte . $remaining;

        if (strlen($response) < 6) {
            return ['error' => 'Response terlalu pendek'];
        }

        return [
            'length' => ord($response[0]),
            'address' => ord($response[1]),
            'command' => ord($response[2]),
            'status' => ord($response[3]),
            'data' => substr($response, 4, ord($response[0]) - 5),
            'raw' => bin2hex($response),
        ];
    }

    /**
     * Set reader power (0-30)
     * Semakin besar = jarak baca semakin jauh
     */
    public function setPower(int $power): array
    {
        if ($power < 0 || $power > 30) {
            return ['error' => 'Power harus antara 0-30'];
        }

        $response = $this->sendCommand(0x2F, [$power]);

        if (isset($response['error'])) {
            return $response;
        }

        return [
            'success' => $response['status'] === 0x00,
            'power' => $power,
            'status' => $response['status'],
            'message' => $response['status'] === 0x00
                ? "Power berhasil diubah ke $power"
                : "Gagal mengubah power (status: " . dechex($response['status']) . ")",
        ];
    }

    /**
     * Get reader information (address, firmware, power, etc.)
     */
    public function getReaderInfo(): array
    {
        $response = $this->sendCommand(0x21);

        if (isset($response['error'])) {
            return $response;
        }

        if ($response['status'] !== 0x00) {
            return ['error' => 'Gagal mendapatkan info reader'];
        }

        $data = $response['data'];

        return [
            'success' => true,
            'version' => sprintf('%d.%d', ord($data[0]), ord($data[1])),
            'type' => ord($data[2]),
            'protocol' => ord($data[3]),
            'address' => ord($data[4]),
            'power' => ord($data[5]),
        ];
    }

    /**
     * Check if reader is reachable
     */
    public function ping(): array
    {
        $socket = @fsockopen($this->ip, $this->port, $errno, $errstr, 2);
        if (!$socket) {
            return [
                'online' => false,
                'ip' => $this->ip,
                'port' => $this->port,
                'error' => $errstr,
            ];
        }
        fclose($socket);

        return [
            'online' => true,
            'ip' => $this->ip,
            'port' => $this->port,
        ];
    }
}
