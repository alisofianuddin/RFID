"""
Diagnostik lengkap HW-VX6336: Coba berbagai address + DTR/RTS control
"""
import serial
import time
from utils import calculate_checksum

COM_PORT = 'COM7'
BAUD_RATE = 57600

def build_raw_command(cmd, address=0xFF, data=None):
    """Build a raw command frame"""
    if data is None:
        data = []
    payload = [address, cmd] + list(data)
    length = len(payload) + 2  # +2 for CRC16
    frame = bytearray([length]) + bytearray(payload)
    crc = calculate_checksum(frame)
    frame.extend(crc)
    return bytes(frame)

# Daftar command yang akan dicoba
TESTS = [
    ("INVENTORY (addr=0xFF)", 0x01, 0xFF),
    ("INVENTORY (addr=0x00)", 0x01, 0x00),
    ("GET_WORK_MODE (addr=0xFF)", 0x36, 0xFF),
    ("GET_WORK_MODE (addr=0x00)", 0x36, 0x00),
    ("GET_FIRMWARE (addr=0xFF)", 0x03, 0xFF),
]

# Daftar DTR/RTS combinations  
DTR_RTS_COMBOS = [
    (True, True, "DTR=ON, RTS=ON"),
    (True, False, "DTR=ON, RTS=OFF"),
    (False, True, "DTR=OFF, RTS=ON"),
    (False, False, "DTR=OFF, RTS=OFF"),
]

print("=" * 60)
print("  Diagnostik HW-VX6336 - Tes Lengkap")
print(f"  Port: {COM_PORT} | Baud: {BAUD_RATE}")
print("=" * 60)

for dtr, rts, label in DTR_RTS_COMBOS:
    print(f"\n{'='*60}")
    print(f"  [{label}]")
    print(f"{'='*60}")
    
    try:
        ser = serial.Serial(
            port=COM_PORT,
            baudrate=BAUD_RATE,
            timeout=2,
            write_timeout=2,
            bytesize=serial.EIGHTBITS,
            parity=serial.PARITY_NONE,
            stopbits=serial.STOPBITS_ONE
        )
        ser.dtr = dtr
        ser.rts = rts
        ser.reset_input_buffer()
        ser.reset_output_buffer()
        time.sleep(0.5)  # Beri waktu setelah set DTR/RTS
        
        # Cek apakah ada data masuk tanpa kirim command (active mode check)
        incoming = ser.read(50)
        if incoming:
            print(f"  📥 Data masuk spontan ({len(incoming)} bytes): {incoming.hex(' ')}")
        
        for name, cmd, addr in TESTS:
            raw_cmd = build_raw_command(cmd, address=addr)
            print(f"\n  [{name}]")
            print(f"    TX: {raw_cmd.hex(' ')}")
            
            ser.reset_input_buffer()
            ser.write(raw_cmd)
            ser.flush()
            time.sleep(0.3)
            
            response = ser.read(100)
            if response:
                print(f"    ✅ RX ({len(response)} bytes): {response.hex(' ')}")
            else:
                print(f"    ❌ Tidak ada respons")
        
        ser.close()
        
    except Exception as e:
        print(f"  ❌ Error: {e}")

print(f"\n{'='*60}")
print("  Selesai!")
print(f"{'='*60}")
