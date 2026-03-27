<?php

namespace App\Http\Controllers;

use App\Models\RfidCard;
use App\Models\RfidLog;
use App\Services\RfidReaderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RfidController extends Controller
{
    /**
     * Menerima scan dari RFID reader (via Python script)
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'uid' => 'required|string|max:50',
        ]);

        $uid = strtoupper(trim($request->uid));

        // Cek apakah card terdaftar
        $card = RfidCard::where('uid', $uid)->first();

        // Simpan log
        $log = RfidLog::create([
            'rfid_card_id' => $card?->id,
            'uid' => $uid,
            'status' => $card ? 'registered' : 'unregistered',
            'scanned_at' => now(),
        ]);

        // Load relasi card
        $log->load('card');

        // Kirim data ke Web WIP secara synchronous / fire-and-forget
        try {
            $wipUrl = env('WIP_API_URL');
            if ($wipUrl) {
                \Illuminate\Support\Facades\Http::timeout(3)->post($wipUrl, [
                    'uid' => $uid,
                    'bn' => $card?->bn,
                    'status' => $log->status,
                    'scanned_at' => $log->scanned_at->toDateTimeString(),
                ]);
            }
        } catch (\Exception $e) {
            // Log error atau abaikan agar tidak mengganggu proses lokal
            \Illuminate\Support\Facades\Log::error("Gagal mengirim ke Web WIP: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => $card
                ? "Card terdaftar: (BN: {$card->bn})"
                : "Card tidak terdaftar (UID: {$uid})",
            'data' => [
                'log_id' => $log->id,
                'uid' => $uid,
                'bn' => $card?->bn ?? '-',
                'scan_status' => $log->status,
                'scanned_at' => $log->scanned_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Menerima scan dari RFID reader khusus PENDAFTARAN (HW-VX6336)
     */
    public function registerScan(Request $request): JsonResponse
    {
        $request->validate([
            'uid' => 'required|string|max:50',
        ]);

        $uid = strtoupper(trim($request->uid));

        // Simpan ke cache selama 2 menit untuk ditarik oleh frontend
        \Illuminate\Support\Facades\Cache::put('last_register_scan', $uid, now()->addMinutes(2));

        return response()->json([
            'success' => true,
            'message' => 'UID terdeteksi untuk pendaftaran',
            'uid' => $uid
        ]);
    }

    /**
     * Polling dari frontend untuk mengambil hasil scan pendaftaran terbaru
     */
    public function getRegisterScan(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'uid' => \Illuminate\Support\Facades\Cache::get('last_register_scan', null)
        ]);
    }

    /**
     * Clear cache setelah frontend berhasil membaca UID (opsional)
     */
    public function clearRegisterScan(): JsonResponse
    {
        \Illuminate\Support\Facades\Cache::forget('last_register_scan');
        return response()->json(['success' => true]);
    }

    /**
     * Ambil log scan terbaru
     */
    public function logs(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $lastId = $request->get('last_id', 0);

        $query = RfidLog::with('card')
            ->orderBy('id', 'desc');

        // Untuk polling: hanya ambil data baru setelah last_id
        if ($lastId > 0) {
            $query->where('id', '>', $lastId);
        }

        $logs = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'uid' => $log->uid,
                    'bn' => $log->card?->bn ?? '-',
                    'status' => $log->status,
                    'scanned_at' => $log->scanned_at->format('Y-m-d H:i:s'),
                ];
            }),
            'latest_id' => $logs->first()?->id ?? $lastId,
        ]);
    }

    /**
     * List semua card terdaftar
     */
    public function cardIndex(): JsonResponse
    {
        $cards = RfidCard::withCount('logs')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cards,
        ]);
    }

    /**
     * Register card baru
     */
    public function cardStore(Request $request): JsonResponse
    {
        $request->validate([
            'uid' => 'required|string|max:50|unique:rfid_cards,uid',
            'bn' => 'required|string|max:255',
        ]);

        $card = RfidCard::create([
            'uid' => strtoupper(trim($request->uid)),
            'bn' => $request->bn,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card berhasil didaftarkan!',
            'data' => $card,
        ], 201);
    }

    /**
     * Update card
     */
    public function cardUpdate(Request $request, RfidCard $card): JsonResponse
    {
        $request->validate([
            'bn' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $card->update($request->only(['bn', 'status']));

        return response()->json([
            'success' => true,
            'message' => 'Card berhasil diupdate!',
            'data' => $card,
        ]);
    }

    /**
     * Hapus card
     */
    public function cardDestroy(RfidCard $card): JsonResponse
    {
        $card->delete();

        return response()->json([
            'success' => true,
            'message' => 'Card berhasil dihapus!',
        ]);
    }

    /**
     * Statistik dashboard
     */
    public function stats(): JsonResponse
    {
        $today = now()->startOfDay();

        return response()->json([
            'success' => true,
            'data' => [
                'total_cards' => RfidCard::where('status', 'active')->count(),
                'total_scans_today' => RfidLog::where('scanned_at', '>=', $today)->count(),
                'registered_scans_today' => RfidLog::where('scanned_at', '>=', $today)
                    ->where('status', 'registered')->count(),
                'unregistered_scans_today' => RfidLog::where('scanned_at', '>=', $today)
                    ->where('status', 'unregistered')->count(),
                'last_scan' => RfidLog::with('card')
                    ->orderBy('id', 'desc')
                    ->first()?->scanned_at?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Ambil info reader — via command queue ke Python (Async)
     */
    public function readerInfo(): JsonResponse
    {
        $service = new RfidReaderService();
        $result = $service->getReaderInfo();

        return response()->json([
            'success' => true,
            'cmd_id' => $result['cmd_id'] ?? null,
            'message' => 'Requested reader info'
        ]);
    }

    /**
     * Set power reader (0-30) — via command queue ke Python (Async)
     */
    public function setReaderPower(Request $request): JsonResponse
    {
        $request->validate([
            'power' => 'required|integer|min:0|max:30',
        ]);

        $service = new RfidReaderService();
        $result = $service->setPower((int) $request->power);

        return response()->json([
            'success' => true,
            'cmd_id' => $result['cmd_id'] ?? null,
            'message' => 'Power update queued'
        ]);
    }

    /**
     * Cek hasil eksekusi command (Polling endpoint)
     */
    public function getCommandResult(string $cmdId): JsonResponse
    {
        $service = new RfidReaderService();
        $result = $service->getCommandResult($cmdId);

        return response()->json($result);
    }

    /**
     * Cek status koneksi reader — via heartbeat dari Python
     */
    public function readerStatus(): JsonResponse
    {
        $service = new RfidReaderService();
        $data = $service->ping();

        return response()->json([
            'success' => $data['online'],
            'data' => $data,
        ]);
    }

    // =============================================
    // Endpoints untuk Python register_reader.py
    // =============================================

    /**
     * Python polling: ambil pending command
     */
    public function getReaderCommand(): JsonResponse
    {
        $cmd = \Illuminate\Support\Facades\Cache::get('rfid_reader_command');

        return response()->json([
            'success' => true,
            'command' => $cmd,
        ]);
    }

    /**
     * Python: kirim hasil eksekusi command
     */
    public function postReaderResult(Request $request): JsonResponse
    {
        $data = $request->all();
        \Illuminate\Support\Facades\Cache::put('rfid_reader_result', $data, now()->addMinutes(1));

        return response()->json(['success' => true]);
    }

    /**
     * Python: kirim heartbeat (status online + info reader)
     */
    public function readerHeartbeat(Request $request): JsonResponse
    {
        $data = $request->all();
        \Illuminate\Support\Facades\Cache::put('rfid_reader_heartbeat', $data, now()->addSeconds(15));

        return response()->json(['success' => true]);
    }
    
    // =============================================
    // Endpoints untuk Python main.py (Scan Reader)
    // =============================================
    
    public function scanReaderStatus(): JsonResponse
    {
        $heartbeat = \Illuminate\Support\Facades\Cache::get('rfid_scan_reader_heartbeat');
        if ($heartbeat) {
            return response()->json([
                'success' => true,
                'data' => [
                    'online' => true,
                    'ip' => $heartbeat['ip'] ?? '192.168.1.190',
                    'port' => $heartbeat['port'] ?? 6000,
                    'version' => $heartbeat['version'] ?? null,
                    'power' => $heartbeat['power'] ?? null,
                ],
            ]);
        }
        
        return response()->json([
            'success' => false,
            'data' => [
                'online' => false,
                'ip' => '192.168.1.190',
                'port' => 6000,
                'error' => 'main.py tidak berjalan',
            ],
        ]);
    }
    
    public function setScanReaderPower(Request $request): JsonResponse
    {
        $request->validate([
            'power' => 'required|integer|min:0|max:30',
        ]);

        $cmdId = uniqid('cmd_scan_');
        $cmd = [
            'id' => $cmdId,
            'command' => 'set_power',
            'params' => ['power' => (int) $request->power],
            'queued_at' => now()->toDateTimeString(),
        ];
        \Illuminate\Support\Facades\Cache::put('rfid_scan_reader_command', $cmd, now()->addMinutes(1));

        return response()->json([
            'success' => true,
            'cmd_id' => $cmdId,
            'message' => 'Power update queued for scan reader'
        ]);
    }
    
    public function getScanReaderCommand(): JsonResponse
    {
        $cmd = \Illuminate\Support\Facades\Cache::get('rfid_scan_reader_command');
        return response()->json([
            'success' => true,
            'command' => $cmd,
        ]);
    }
    
    public function getScanCommandResult(string $cmdId): JsonResponse
    {
        $result = \Illuminate\Support\Facades\Cache::get('rfid_scan_reader_result');
        if ($result && ($result['cmd_id'] ?? '') === $cmdId) {
            return response()->json($result);
        }
        return response()->json(['success' => false, 'status' => 'pending']);
    }

    public function postScanReaderResult(Request $request): JsonResponse
    {
        $data = $request->all();
        \Illuminate\Support\Facades\Cache::put('rfid_scan_reader_result', $data, now()->addMinutes(1));
        return response()->json(['success' => true]);
    }

    public function scanReaderHeartbeat(Request $request): JsonResponse
    {
        $data = $request->all();
        \Illuminate\Support\Facades\Cache::put('rfid_scan_reader_heartbeat', $data, now()->addSeconds(15));
        return response()->json(['success' => true]);
    }
    
    public function getScanReaderConfig(): JsonResponse
    {
        $config = \Illuminate\Support\Facades\Cache::get('rfid_scan_reader_config', [
            'ip' => '192.168.1.190',
            'port' => 6000,
            'time' => 500,
            'power' => 15
        ]);
        return response()->json(['success' => true, 'data' => $config]);
    }
    
    public function saveScanReaderConfig(Request $request): JsonResponse
    {
        $request->validate([
            'ip' => 'required|string',
            'port' => 'required|integer',
            'time' => 'required|integer',
            'power' => 'required|integer|min:0|max:30',
        ]);
        
        $config = $request->only(['ip', 'port', 'time', 'power']);
        \Illuminate\Support\Facades\Cache::forever('rfid_scan_reader_config', $config);
        
        // Also queue a command to set power right away if reader is running
        $cmdId = uniqid('cmd_scan_');
        $cmd = [
            'id' => $cmdId,
            'command' => 'set_power',
            'params' => ['power' => (int) $request->power],
            'queued_at' => now()->toDateTimeString(),
        ];
        \Illuminate\Support\Facades\Cache::put('rfid_scan_reader_command', $cmd, now()->addMinutes(1));
        
        return response()->json(['success' => true, 'message' => 'Konfigurasi disimpan']);
    }

    public function getScanMultiReaderConfig(): JsonResponse
    {
        $configs = [
            [
                'id' => 1,
                'name' => 'Pintu Utama (Gate A)',
                'ip' => '192.168.1.190',
                'port' => 6000,
                'baudRate' => 115200,
                'timeCard' => 500,
                'type' => 'Global Scan',
                'status' => 'offline',
                'power' => 15
            ]
        ];

        if (\Illuminate\Support\Facades\Storage::disk('local')->exists('scan_readers.json')) {
            $json = \Illuminate\Support\Facades\Storage::disk('local')->get('scan_readers.json');
            $configs = json_decode($json, true) ?: $configs;
        }

        return response()->json(['success' => true, 'data' => $configs]);
    }

    public function saveScanMultiReaderConfig(Request $request): JsonResponse
    {
        $request->validate([
            'readers' => 'present|array',
            'readers.*.id' => 'required',
            'readers.*.name' => 'required|string',
            'readers.*.ip' => 'required|string',
            'readers.*.port' => 'required',
            'readers.*.baudRate' => 'required',
            'readers.*.timeCard' => 'required',
            'readers.*.type' => 'required|string',
        ]);

        $readers = $request->input('readers', []);
        
        \Illuminate\Support\Facades\Storage::disk('local')->put('scan_readers.json', json_encode($readers, JSON_PRETTY_PRINT));

        // Optional: Sync ke backward-compatible single reader config (biar main.py yg lama masih jalan sementara)
        if (count($readers) > 0) {
            $first = $readers[0];
            $legacyConfig = [
                'ip' => $first['ip'],
                'port' => (int) $first['port'],
                'time' => (int) $first['timeCard'],
                'power' => (int) ($first['power'] ?? 15),
            ];
            // Legacy config tetap di cache karena hanya jembatan sementara untuk main.py berjalan
            \Illuminate\Support\Facades\Cache::forever('rfid_scan_reader_config', $legacyConfig);
        }

        return response()->json(['success' => true, 'message' => 'Konfigurasi Multi-Reader berhasil disimpan permanen']);
    }
}

