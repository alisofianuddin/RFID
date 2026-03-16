import sys
import time
import requests
import json
from typing import Iterator

from response import hex_readable, Response, WorkMode, InventoryWorkMode, InventoryMemoryBank
from transport import SerialTransport, TcpTransport
from reader import Reader
from command import Command

# KONFIGURASI READER PENDAFTARAN (HW-VX6336)

COM_PORT = 'COM7'
BAUD_RATE = 9600

transport = SerialTransport(COM_PORT, BAUD_RATE)
reader = Reader(transport)

# Laravel API URLs
BASE_URL = "http://127.0.0.1:8000/api/rfid"
REGISTER_SCAN_URL = f"{BASE_URL}/register-scan"
HEARTBEAT_URL = f"{BASE_URL}/reader/heartbeat"
COMMAND_URL = f"{BASE_URL}/reader/command"
RESULT_URL = f"{BASE_URL}/reader/command-result"

# ============================================================
# FUNGSI KIRIM KE LARAVEL
# ============================================================

def send_to_laravel(uid: str):
    """Kirim UID tag ke Laravel API untuk ditangkap form pendaftaran"""
    try:
        response = requests.post(REGISTER_SCAN_URL, json={"uid": uid}, timeout=5)
        if response.status_code == 200:
            print(f"  → Sukses dikirim ke Form Pendaftaran: UID {uid}")
        else:
            print(f"  → API Error: HTTP {response.status_code}")
    except requests.exceptions.ConnectionError:
        print(f"  → API tidak tersedia (Laravel belum jalan?)")
    except Exception as e:
        print(f"  → API Error: {e}")


def send_heartbeat(power=None, version=None):
    """Kirim heartbeat ke Laravel supaya dashboard tahu reader online"""
    try:
        data = {
            "com_port": COM_PORT,
            "baud_rate": BAUD_RATE,
            "online": True,
        }
        if power is not None:
            data["power"] = power
        if version is not None:
            data["version"] = version
        r = requests.post(HEARTBEAT_URL, json=data, timeout=3)
        now_str = time.strftime("%H:%M:%S")
        print(f"  [HB] Heartbeat sent at {now_str} (HTTP {r.status_code})")
    except Exception as e:
        print(f"  [HB] Heartbeat gagal: {e}")


def check_commands():
    """Polling perintah dari dashboard (set_power, get_info, dll)"""
    try:
        res = requests.get(COMMAND_URL, timeout=3)
        json_data = res.json()
        cmd = json_data.get("command")
        if cmd:
            print(f"  [CMD] Perintah diterima: {cmd}")
            execute_command(cmd)
    except Exception as e:
        pass  # Polling gagal bukan masalah kritis


def execute_command(cmd):
    """Eksekusi perintah dari dashboard"""
    global reader_power, reader_version
    cmd_id = cmd.get("id", "")
    command = cmd.get("command", "")
    params = cmd.get("params", {})

    print(f"\n[CMD] Menerima perintah: {command} {params}")

    result = {"cmd_id": cmd_id, "command": command}

    try:
        if command == "set_power":
            power = int(params.get("power", 15))
            resp = reader.set_power(power)
            reader_power = power # Update global state
            result["success"] = True
            result["power"] = power
            result["message"] = f"Power berhasil diubah ke {power}"
            print(f"  → Power diubah ke {power} ✓")

        elif command == "get_info":
            # Kirim CMD_GET_READER_INFO (0x21)
            info_cmd = Command(0x21)
            reader._Reader__send_request(info_cmd)
            raw = reader._Reader__get_response()
            if raw:
                resp = Response(raw)
                data = resp.data
                if len(data) >= 6:
                    reader_version = f"{data[0]}.{data[1]:02d}"
                    reader_power = data[5]
                    result["success"] = True
                    result["online"] = True
                    result["version"] = reader_version
                    result["type"] = data[2]
                    result["protocol"] = data[3]
                    result["address"] = data[4]
                    result["power"] = reader_power
                    result["ip"] = COM_PORT
                    result["port"] = BAUD_RATE
                else:
                    result["success"] = False
                    result["error"] = "Data terlalu pendek"
            else:
                result["success"] = False
                result["error"] = "Reader tidak merespons"

        else:
            result["success"] = False
            result["error"] = f"Perintah tidak dikenal: {command}"

    except Exception as e:
        result["success"] = False
        result["error"] = str(e)
        print(f"  → Error: {e}")

    # Kirim hasil ke Laravel
    try:
        requests.post(RESULT_URL, json=result, timeout=5)
    except Exception as e:
        print(f"  → Gagal kirim hasil: {e}")


# ============================================================
# AMBIL INFO READER AWAL
# ============================================================
reader_power = None
reader_version = None

try:
    info_cmd = Command(0x21)
    reader._Reader__send_request(info_cmd)
    raw = reader._Reader__get_response()
    if raw:
        resp = Response(raw)
        data = resp.data
        if len(data) >= 6:
            reader_version = f"{data[0]}.{data[1]:02d}"
            reader_power = data[5]
            print(f"  Reader: HW-VX6336 v{reader_version}")
            print(f"  Power: {reader_power}")
except Exception:
    pass


# ============================================================
# JALANKAN READER MODE ACTIVE
# ============================================================

print("=" * 50)
print("  RFID Reader HW-VX6336 (PENDAFTARAN)")
print(f"  COM Port: {COM_PORT} | Baud: {BAUD_RATE}")
print("  Tekan Ctrl+C untuk berhenti")
print("=" * 50)

last_tag = None
last_tag_time = 0
last_heartbeat = 0
HEARTBEAT_INTERVAL = 3  # Kirim heartbeat setiap 3 detik

try:
    print("\n[Answer Mode Pendaftaran] Menunggu tap kartu baru...\n")

    while True:
        # 1. Scan kartu
        try:
            tags = list(reader.inventory_answer_mode())
        except Exception:
            tags = []
        for tag in tags:
            tag_hex = hex_readable(tag)
            tag_uid = hex_readable(tag, "")

            now = time.time()
            if tag_hex == last_tag and (now - last_tag_time) < 2:
                continue

            last_tag = tag_hex
            last_tag_time = now

            print(f"[TAG] {tag_hex}")
            send_to_laravel(tag_uid)

        # 2. Heartbeat ke Laravel (setiap 5 detik)
        now = time.time()
        if now - last_heartbeat >= HEARTBEAT_INTERVAL:
            send_heartbeat(power=reader_power, version=reader_version)
            last_heartbeat = now

        # 3. Cek perintah dari dashboard (setiap scan cycle)
        check_commands()

        time.sleep(0.3)  # Delay antar scan

except KeyboardInterrupt:
    print("\n\nReader Pendaftaran dihentikan oleh user.")
finally:
    reader.close()
