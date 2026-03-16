<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Service untuk komunikasi dengan RFID Reader UHFReader09
 * Menggunakan cache-based command queue:
 * - Dashboard tulis perintah ke cache
 * - Python register_reader.py polling perintah, eksekusi via COM port, tulis hasil ke cache
 * - Dashboard polling hasil dari cache
 */
class RfidReaderService
{
    /**
     * Queue command untuk dieksekusi oleh register_reader.py
     * Non-blocking: langsung return CMD ID
     */
    public function queueCommand(string $command, array $params = []): array
    {
        $cmdId = uniqid('cmd_');
        $cmd = [
            'id' => $cmdId,
            'command' => $command,
            'params' => $params,
            'queued_at' => now()->toDateTimeString(),
        ];

        Cache::put('rfid_reader_command', $cmd, now()->addMinutes(1));

        return [
            'success' => true,
            'cmd_id' => $cmdId,
            'message' => 'Perintah dikirim ke antrean'
        ];
    }

    /**
     * Ambil hasil eksekusi command
     */
    public function getCommandResult(string $cmdId): array
    {
        $result = Cache::get('rfid_reader_result');
        if ($result && ($result['cmd_id'] ?? '') === $cmdId) {
            // Hapus cache perintah jika sudah selesai diproses (opsional, tapi bagus untuk cleanup)
            // Cache::forget('rfid_reader_command'); 
            return $result;
        }

        return ['success' => false, 'status' => 'pending'];
    }

    /**
     * Set reader power (0-30)
     */
    public function setPower(int $power): array
    {
        if ($power < 0 || $power > 30) {
            return ['success' => false, 'error' => 'Power harus antara 0-30'];
        }

        return $this->queueCommand('set_power', ['power' => $power]);
    }

    /**
     * Get reader info
     */
    public function getReaderInfo(): array
    {
        return $this->queueCommand('get_info');
    }

    /**
     * Cek status reader — cukup cek apakah Python script aktif
     * Python heartbeat disimpan di cache setiap 3 detik
     */
    public function ping(): array
    {
        $heartbeat = Cache::get('rfid_reader_heartbeat');

        if ($heartbeat) {
            return [
                'online' => true,
                'ip' => $heartbeat['com_port'] ?? 'COM7',
                'port' => $heartbeat['baud_rate'] ?? 9600,
                'version' => $heartbeat['version'] ?? null,
                'power' => $heartbeat['power'] ?? null,
            ];
        }

        return [
            'online' => false,
            'ip' => 'COM7',
            'port' => 9600,
            'error' => 'register_reader.py tidak berjalan',
        ];
    }
}
