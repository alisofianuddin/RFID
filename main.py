import sys
import time
import requests
from typing import Iterator

from response import hex_readable, Response, WorkMode, InventoryWorkMode, InventoryMemoryBank
from transport import SerialTransport, TcpTransport
from reader import Reader

# ============================================================
# KONFIGURASI - Sesuaikan dengan reader kamu
# ============================================================

# Pilih salah satu transport (comment/uncomment):

# Opsi 1: TCP/IP (dari screenshot demo: 192.168.1.190:6000)
transport = TcpTransport('192.168.1.190', 6000)

# Opsi 2: Serial COM port
# transport = SerialTransport('COM2', 57600)

reader = Reader(transport)

# Laravel API URL
API_URL = "http://127.0.0.1:8000/api/rfid/scan"

# ============================================================


def send_to_laravel(uid: str):
    """Kirim UID tag ke Laravel API"""
    try:
        response = requests.post(API_URL, json={"uid": uid}, timeout=5)
        if response.status_code == 200:
            data = response.json()
            nama = data.get('data', {}).get('card', {}).get('nama', 'Tidak terdaftar')
            status = data.get('data', {}).get('status', 'unknown')
            print(f"  → API: {status} | Nama: {nama}")
        else:
            print(f"  → API Error: HTTP {response.status_code}")
    except requests.exceptions.ConnectionError:
        print(f"  → API tidak tersedia (Laravel belum jalan?)")
    except Exception as e:
        print(f"  → API Error: {e}")


# ============================================================
# PILIH MODE OPERASI (uncomment salah satu bagian)
# ============================================================

print("=" * 50)
print("  RFID Reader HW-VX6330K v2")
print("  Tekan Ctrl+C untuk berhenti")
print("=" * 50)

#########################################################
# MODE 1: Active Mode - Reader auto kirim data tag
#         (Cocok untuk monitoring real-time)
#########################################################

last_tag = None
last_tag_time = 0

try:
    # Auto-set reader ke Active Mode + INVENTORY_MULTIPLE setiap startup
    print("[SETUP] Mengaktifkan Active Mode...")
    work_mode = reader.work_mode()
    work_mode.inventory_work_mode = InventoryWorkMode.ACTIVE_MODE
    work_mode.memory_bank = InventoryMemoryBank.INVENTORY_MULTIPLE
    work_mode.work_mode_state.beep = True
    reader.set_work_mode(work_mode)
    print("[SETUP] Active Mode aktif!")

    # Tunggu reader stabil setelah set mode
    time.sleep(1)

    print("\n[Active Mode] Menunggu tag RFID...\n")
    responses: Iterator[Response] = reader.inventory_active_mode()
    for response in responses:
        tag: bytes = response.data
        tag_hex: str = hex_readable(tag)
        tag_uid: str = hex_readable(tag, "")  # Tanpa spasi, untuk API

        # Filter duplikat dalam 2 detik
        now = time.time()
        if tag_hex == last_tag and (now - last_tag_time) < 2:
            continue

        last_tag = tag_hex
        last_tag_time = now

        print(f"[TAG] {tag_hex}")
        send_to_laravel(tag_uid)

except KeyboardInterrupt:
    print("\n\nReader dihentikan oleh user.")

#########################################################
# MODE 2: Answer Mode - Host kirim perintah, reader balas
#         (Uncomment bagian ini, comment bagian MODE 1)
#########################################################

# try:
#     print("\n[Answer Mode] Scanning tag...\n")
#     while True:
#         tags: Iterator[bytes] = reader.inventory_answer_mode()
#         for tag in tags:
#             tag_hex: str = hex_readable(tag)
#             tag_uid: str = hex_readable(tag, "")
#             print(f"[TAG] {tag_hex}")
#             send_to_laravel(tag_uid)
#         time.sleep(0.5)  # Delay antar scan
# except KeyboardInterrupt:
#     print("\n\nReader dihentikan oleh user.")

#########################################################
# UTILS: Get/Set Work Mode, Set Power, dll
#########################################################

# # Cek work mode saat ini
# work_mode: WorkMode = reader.work_mode()
# print(work_mode)

# # Set ke Active Mode
# work_mode.inventory_work_mode = InventoryWorkMode.ACTIVE_MODE
# work_mode.work_mode_state.beep = True
# res = reader.set_work_mode(work_mode)
# print(res)

# # Set ke Answer Mode
# work_mode.inventory_work_mode = InventoryWorkMode.ANSWER_MODE
# res = reader.set_work_mode(work_mode)
# print(res)

# # Set power (0-30)
# res = reader.set_power(28)
# print(res)

reader.close()
