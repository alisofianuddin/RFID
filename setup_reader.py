"""
Setup Reader - Ubah WorkMode agar baca Full EPC (12 byte)
Jalankan 1x saja: python setup_reader.py
"""

from response import hex_readable, WorkMode, InventoryWorkMode, InventoryMemoryBank
from transport import TcpTransport
from reader import Reader

# Koneksi ke reader
transport = TcpTransport('192.168.1.190', 6000)
reader = Reader(transport)

print()
print("=" * 50)
print("  Setup RFID Reader HW-VX6330K v2")
print("=" * 50)

# 1. Baca WorkMode saat ini
print()
print("[INFO] WorkMode saat ini:")
print("-" * 40)
work_mode: WorkMode = reader.work_mode()
print(work_mode)

# 2. Ubah setting agar baca Full EPC
print()
print("[SETUP] Mengubah setting...")
print("-" * 40)

# Set memory bank ke INVENTORY_MULTIPLE (inventory standard, return full EPC)
work_mode.memory_bank = InventoryMemoryBank.INVENTORY_MULTIPLE

# Set ke Active Mode (auto kirim data saat tag terdeteksi)
work_mode.inventory_work_mode = InventoryWorkMode.ACTIVE_MODE

# Aktifkan buzzer
work_mode.work_mode_state.beep = True

# Simpan ke reader
response = reader.set_work_mode(work_mode)
print(f"Set WorkMode: Status {hex_readable(response.status)}")

if response.status == 0x00:
    print("[OK] Setting berhasil disimpan!")
else:
    print("[GAGAL] Gagal menyimpan setting")

# 3. Verifikasi setting baru
print()
print("[INFO] WorkMode setelah diubah:")
print("-" * 40)
work_mode_new: WorkMode = reader.work_mode()
print(work_mode_new)

reader.close()

print()
print("=" * 50)
print("  Setup selesai! Jalankan: python main.py")
print("=" * 50)
print()
