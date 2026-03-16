import sys
import time
import requests
from typing import Iterator

from response import hex_readable, Response, WorkMode, InventoryWorkMode, InventoryMemoryBank
from transport import SerialTransport, TcpTransport
from reader import Reader
from command import Command

# ============================================================
# KONFIGURASI - Reader Scan HW-VX6330K (SCAN WIP)
# Koneksi via TCP/IP (sesuai demo Electron HW-VX6330K v2.8)
# ============================================================

# TCP/IP — Reader scan terhubung via ethernet
READER_IP = '192.168.1.190'
READER_PORT = 6000

# Laravel API URL
API_URL = "http://127.0.0.1:8000/api/rfid/scan"
API_CMD_URL = "http://127.0.0.1:8000/api/rfid/scan-reader/command"
API_RES_URL = "http://127.0.0.1:8000/api/rfid/scan-reader/command-result"
API_HB_URL = "http://127.0.0.1:8000/api/rfid/scan-reader/heartbeat"
API_CONFIG_URL = "http://127.0.0.1:8000/api/rfid/scan-reader/config"

# ============================================================

def fetch_config():
    """Mengambil setingan IP, port, dan power dari database (Laravel)"""
    global READER_IP, READER_PORT, scan_reader_power
    try:
        res = requests.get(API_CONFIG_URL, timeout=3)
        if res.status_code == 200:
            data = res.json().get('data', {})
            READER_IP = data.get('ip', READER_IP)
            READER_PORT = data.get('port', READER_PORT)
            
            # Inisiasi awal power jika belum didapat dari hardware
            if scan_reader_power is None and 'power' in data:
                scan_reader_power = data['power']
                
            print(f"  [CONFIG] Server Setting: {READER_IP}:{READER_PORT} (Target Power: {data.get('power', 15)})")
    except Exception as e:
        print(f"  [WARN] Konfigurasi server tidak tercapai, pakai default {READER_IP}:{READER_PORT}")



def connect_reader():
    """Buat koneksi TCP ke reader dan set ke Answer Mode, return (transport, reader) atau None jika gagal"""
    global scan_reader_power, scan_reader_version
    try:
        transport = TcpTransport(READER_IP, READER_PORT)
        reader = Reader(transport)
        
        # Ambil info (versi & power bawaan)
        try:
            info_cmd = Command(0x21)
            reader._Reader__send_request(info_cmd)
            raw = reader._Reader__get_response()
            if raw:
                resp = Response(raw)
                rdata = resp.data
                if len(rdata) >= 6:
                    scan_reader_version = f"{rdata[0]}.{rdata[1]:02d}"
                    scan_reader_power = rdata[5]
                    print(f"  [INFO] Reader API: v{scan_reader_version}, power {scan_reader_power}")
        except Exception as e:
            print(f"  [WARN] Gagal membaca info awal reader: {e}")
        
        # Set explicitly to Answer Mode
        mode = reader.work_mode()
        if mode.inventory_work_mode != InventoryWorkMode.ANSWER_MODE:
            mode.inventory_work_mode = InventoryWorkMode.ANSWER_MODE
            reader.set_work_mode(mode)
            print("  [INFO] Reader diset ke Answer Mode")
            
        return transport, reader
    except Exception as e:
        print(f"  [ERROR] Gagal konek ke reader: {e}")
        return None

def reader_worker():
    """Background worker untuk poll command dan kirim heartbeat secara async"""
    global reader, scan_reader_power, scan_reader_version
    
    last_heartbeat = 0
    while True:
        try:
            # 1. Heartbeat
            now = time.time()
            if now - last_heartbeat > 3:  # Tiap 3 detik
                data = {
                    'ip': READER_IP,
                    'port': READER_PORT,
                    'online': reader is not None
                }
                
                # Tambah info jika terhubung
                if reader is not None:
                    if scan_reader_version:
                        data['version'] = scan_reader_version
                    if scan_reader_power is not None:
                        data['power'] = scan_reader_power
                
                requests.post(API_HB_URL, json=data, timeout=2)
                last_heartbeat = now
                
            # 2. Polling Command
            if reader is not None:
                res = requests.get(API_CMD_URL, timeout=2)
                if res.status_code == 200:
                    json_data = res.json()
                    cmd = json_data.get('command')
                    
                    if cmd:
                        cmd_id = cmd.get('id')
                        cmd_name = cmd.get('command')
                        params = cmd.get('params', {})
                        
                        print(f"\n[Command] Menerima instruksi: {cmd_name} ({cmd_id})")
                        
                        result = {'success': False, 'cmd_id': cmd_id, 'status': 'completed'}
                        
                        try:
                            if cmd_name == 'set_power':
                                power = params.get('power', 15)
                                reader.set_power(power)
                                scan_reader_power = power  # Update state secara global
                                print(f"  → Sukses set power ke {power}")
                                result['success'] = True
                                result['message'] = f'Power diubah ke {power}'
                                
                            elif cmd_name == 'get_info':
                                if scan_reader_version and scan_reader_power is not None:
                                    print(f"  → Info: v{scan_reader_version}, power {scan_reader_power}")
                                    result['success'] = True
                                    result['data'] = {'version': scan_reader_version, 'power': scan_reader_power}
                                else:
                                    result['error'] = 'Belum ada data reader'
                                
                            else:
                                print(f"  → Command tidak dikenal: {cmd_name}")
                                result['error'] = 'Command unknown'
                                
                        except (OSError, Exception) as e:
                            print(f"  → Gagal eksekusi {cmd_name}: {e}")
                            result['error'] = str(e)
                            # Jika soket error (misal WinError), trigger reconnect di loop utama dengan menghapus reader
                            if isinstance(e, OSError):
                                try:
                                    reader.close()
                                except:
                                    pass
                                reader = None
                            
                        # Kirim hasil
                        requests.post(API_RES_URL, json=result, timeout=2)
                        
        except requests.exceptions.RequestException:
            pass  # Abaikan error koneksi ke Laravel untuk worker ini
        except Exception as e:
            print(f"[Worker Error] {e}")
            
        time.sleep(1)


def send_to_laravel(uid: str):
    """Kirim UID tag ke Laravel API"""
    try:
        response = requests.post(API_URL, json={"uid": uid}, timeout=5)
        if response.status_code == 200:
            data = response.json()
            bn = data.get('data', {}).get('bn', '-')
            status = data.get('data', {}).get('scan_status', 'unknown')
            msg = data.get('message', '')
            print(f"  → API: {status} | {msg}")
        else:
            print(f"  → API Error: HTTP {response.status_code}")
    except requests.exceptions.ConnectionError:
        print(f"  → API tidak tersedia (Laravel belum jalan?)")
    except Exception as e:
        print(f"  → API Error: {e}")

import threading


# ============================================================
# JALANKAN SCAN READER DENGAN AUTO-RECONNECT
# ============================================================

print("=" * 50)
print(f"  RFID Scan Reader HW-VX6330K (TCP {READER_IP}:{READER_PORT})")
print("  Tekan Ctrl+C untuk berhenti")
print("=" * 50)

last_tag = None
last_tag_time = 0
reader = None
transport = None
scan_reader_power = None
scan_reader_version = None

# Jalankan background worker
import threading
worker_thread = threading.Thread(target=reader_worker, daemon=True)
worker_thread.start()

try:
    # Ambil nilai awal dari Controller config jika Laravel menyala
    fetch_config()
    
    while True:
        # Koneksi ke reader jika belum terhubung
        if reader is None:
            # Refresh config bila sempat putus
            fetch_config()
            print("\n[Connecting] Menghubungkan ke reader...")
            result = connect_reader()
            if result is None:
                print("  Retry dalam 3 detik...")
                time.sleep(3)
                continue
            transport, reader = result
            print("[Answer Mode] Menunggu tap kartu RFID...\n")

        # Scan kartu
        try:
            tags = list(reader.inventory_answer_mode())
        except (IndexError, ValueError, OSError, TimeoutError) as e:
            print(f"\n[WARN] Koneksi terputus: {e}")
            print("  Reconnecting dalam 3 detik...")
            try:
                reader.close()
            except Exception:
                pass
            reader = None
            time.sleep(3)
            continue

        for tag in tags:
            tag_hex = hex_readable(tag)
            tag_uid = hex_readable(tag, "")  # Tanpa spasi, untuk API

            # Filter duplikat dalam 2 detik agar tidak spam API
            now = time.time()
            if tag_hex == last_tag and (now - last_tag_time) < 2:
                continue

            last_tag = tag_hex
            last_tag_time = now

            print(f"[TAG] {tag_hex}")
            send_to_laravel(tag_uid)

        time.sleep(0.3)  # Delay antar polling

except KeyboardInterrupt:
    print("\n\nReader dihentikan oleh user.")
finally:
    if reader:
        reader.close()

