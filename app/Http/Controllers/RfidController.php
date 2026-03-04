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

        return response()->json([
            'success' => true,
            'message' => $card
                ? "Card terdaftar: {$card->nama}"
                : "Card tidak terdaftar (UID: {$uid})",
            'data' => [
                'log_id' => $log->id,
                'uid' => $uid,
                'card_name' => $card?->nama ?? 'Tidak Terdaftar',
                'card_status' => $card?->status ?? 'unknown',
                'scan_status' => $log->status,
                'scanned_at' => $log->scanned_at->format('Y-m-d H:i:s'),
            ]
        ]);
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
                    'card_name' => $log->card?->nama ?? 'Tidak Terdaftar',
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
            'nama' => 'required|string|max:255',
        ]);

        $card = RfidCard::create([
            'uid' => strtoupper(trim($request->uid)),
            'nama' => $request->nama,
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
            'nama' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $card->update($request->only(['nama', 'status']));

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
     * Ambil info reader (termasuk power saat ini)
     */
    public function readerInfo(): JsonResponse
    {
        $service = new RfidReaderService();
        $result = $service->getReaderInfo();

        return response()->json([
            'success' => !isset($result['error']),
            'data' => $result,
        ]);
    }

    /**
     * Set power reader (0-30)
     */
    public function setReaderPower(Request $request): JsonResponse
    {
        $request->validate([
            'power' => 'required|integer|min:0|max:30',
        ]);

        $service = new RfidReaderService();
        $result = $service->setPower((int) $request->power);

        return response()->json([
            'success' => $result['success'] ?? false,
            'data' => $result,
        ]);
    }

    /**
     * Cek status koneksi reader
     */
    public function readerStatus(): JsonResponse
    {
        $service = new RfidReaderService();
        $ping = $service->ping();

        $data = $ping;
        if ($ping['online']) {
            $info = $service->getReaderInfo();
            if (!isset($info['error'])) {
                $data = array_merge($ping, $info);
            }
        }

        return response()->json([
            'success' => $ping['online'],
            'data' => $data,
        ]);
    }
}

