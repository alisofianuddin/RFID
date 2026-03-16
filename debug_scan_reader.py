"""Debug script v2 - output ke file"""
import socket
import time
import sys
sys.path.insert(0, r'd:\Program new windows\Project Laravel\RFID')
from utils import calculate_checksum

READER_IP = '192.168.1.190'
READER_PORT = 6000
LOG_FILE = r'd:\Program new windows\Project Laravel\RFID\debug_log.txt'

log_lines = []

def log(msg):
    print(msg)
    log_lines.append(msg)

def save_log():
    with open(LOG_FILE, 'w', encoding='utf-8') as f:
        f.write('\n'.join(log_lines))
    print(f"\n[LOG saved to {LOG_FILE}]")

def build_command(cmd_byte, reader_addr=0x00, data=None):
    if data is None:
        data = bytearray()
    frame_length = 4 + len(data)
    base = bytearray([frame_length, reader_addr, cmd_byte])
    base.extend(data)
    checksum = calculate_checksum(base)
    base.extend(checksum)
    return bytes(base)

def hex_str(data):
    return ' '.join(f'{b:02X}' for b in data)

try:
    log("=" * 60)
    log(f"DEBUG Reader Scan HW-VX6330K ({READER_IP}:{READER_PORT})")
    log("=" * 60)

    # Test 1: Get Reader Info (addr=0x00)
    log("\n--- TEST 1: Get Reader Info (addr=0x00) ---")
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.settimeout(5)
    sock.connect((READER_IP, READER_PORT))
    time.sleep(0.5)
    cmd = build_command(0x21, reader_addr=0x00)
    log(f"  Kirim: {hex_str(cmd)}")
    sock.sendall(cmd)
    try:
        raw = sock.recv(1024)
        log(f"  Terima ({len(raw)} bytes): {hex_str(raw)}")
    except socket.timeout:
        log("  Timeout")
    except Exception as e:
        log(f"  Error: {e}")
    sock.close()
    time.sleep(1)

    # Test 2: Inventory (addr=0x00) - 3 attempts
    log("\n--- TEST 2: Inventory (addr=0x00) ---")
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.settimeout(3)
    sock.connect((READER_IP, READER_PORT))
    time.sleep(0.5)
    for i in range(3):
        cmd = build_command(0x01, reader_addr=0x00)
        log(f"  [{i+1}] Kirim: {hex_str(cmd)}")
        try:
            sock.sendall(cmd)
        except Exception as e:
            log(f"      Send error: {e}")
            break
        try:
            raw = sock.recv(1024)
            log(f"      Terima ({len(raw)} bytes): {hex_str(raw)}")
        except socket.timeout:
            log("      Timeout")
        except Exception as e:
            log(f"      Error: {e}")
            break
        time.sleep(1)
    sock.close()
    time.sleep(1)

    # Test 3: Inventory (addr=0xFF)
    log("\n--- TEST 3: Inventory (addr=0xFF) ---")
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.settimeout(3)
    sock.connect((READER_IP, READER_PORT))
    time.sleep(0.5)
    for i in range(3):
        cmd = build_command(0x01, reader_addr=0xFF)
        log(f"  [{i+1}] Kirim: {hex_str(cmd)}")
        try:
            sock.sendall(cmd)
        except Exception as e:
            log(f"      Send error: {e}")
            break
        try:
            raw = sock.recv(1024)
            log(f"      Terima ({len(raw)} bytes): {hex_str(raw)}")
        except socket.timeout:
            log("      Timeout")
        except Exception as e:
            log(f"      Error: {e}")
            break
        time.sleep(1)
    sock.close()

    log("\n--- SELESAI ---")

except Exception as e:
    log(f"\n[FATAL ERROR] {e}")

save_log()
