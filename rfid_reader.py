"""
RFID Reader Script — HW-VX6330K v2
===================================
Script Python yang membaca RFID card dari serial port (COM Port)
dan mengirim data ke Laravel API.

Penggunaan:
    python rfid_reader.py              → Gunakan setting dari .env
    python rfid_reader.py --port COM3  → Tentukan COM port
    python rfid_reader.py --baud 9600  → Tentukan baud rate

Kebutuhan:
    pip install pyserial requests python-dotenv
"""

import serial
import serial.tools.list_ports
import requests
import time
import argparse
import sys
import os
from datetime import datetime

# Load .env file
try:
    from dotenv import load_dotenv
    load_dotenv()
except ImportError:
    pass


def get_config():
    """Ambil konfigurasi dari argument atau .env"""
    parser = argparse.ArgumentParser(description='RFID Reader HW-VX6330K v2')
    parser.add_argument('--port', type=str, default=os.getenv('RFID_COM_PORT', 'COM3'),
                        help='Serial port (contoh: COM3, COM4)')
    parser.add_argument('--baud', type=int, default=int(os.getenv('RFID_BAUD_RATE', '9600')),
                        help='Baud rate (contoh: 9600, 115200)')
    parser.add_argument('--url', type=str,
                        default=os.getenv('RFID_API_URL', 'http://127.0.0.1:8000/api/rfid/scan'),
                        help='URL API Laravel')
    return parser.parse_args()


def list_available_ports():
    """Tampilkan daftar COM port yang tersedia"""
    ports = serial.tools.list_ports.comports()
    if not ports:
        print("  ⚠️  Tidak ada COM port yang terdeteksi!")
        print("  → Pastikan RFID reader sudah terhubung via USB")
        print("  → Pastikan driver CH340 sudah terinstall")
        return False

    print("  📋 COM Port yang tersedia:")
    for port in ports:
        print(f"     • {port.device} — {port.description}")
    return True


def send_to_api(uid, api_url):
    """Kirim UID ke Laravel API"""
    try:
        response = requests.post(api_url, json={'uid': uid}, timeout=5)
        data = response.json()

        if data.get('success'):
            card_name = data['data'].get('card_name', 'Unknown')
            status = data['data'].get('scan_status', 'unknown')
            timestamp = data['data'].get('scanned_at', '')

            if status == 'registered':
                print(f"  ✅ TERDAFTAR: {card_name} | UID: {uid} | {timestamp}")
            else:
                print(f"  ⚠️  TIDAK TERDAFTAR | UID: {uid} | {timestamp}")
        else:
            print(f"  ❌ API Error: {data.get('message', 'Unknown error')}")

    except requests.exceptions.ConnectionError:
        print(f"  ❌ Tidak bisa terhubung ke API ({api_url})")
        print(f"     → Pastikan Laravel sudah running: php artisan serve")
    except requests.exceptions.Timeout:
        print(f"  ❌ Request timeout ke API")
    except Exception as e:
        print(f"  ❌ Error: {str(e)}")


def main():
    config = get_config()

    print("=" * 60)
    print("  🔌 RFID Reader — HW-VX6330K v2")
    print("=" * 60)
    print(f"  COM Port  : {config.port}")
    print(f"  Baud Rate : {config.baud}")
    print(f"  API URL   : {config.url}")
    print("-" * 60)

    # List available ports
    list_available_ports()
    print("-" * 60)

    # Auto-reconnect loop
    while True:
        try:
            print(f"\n  📡 Menghubungkan ke {config.port}...")
            ser = serial.Serial(
                port=config.port,
                baudrate=config.baud,
                bytesize=serial.EIGHTBITS,
                parity=serial.PARITY_NONE,
                stopbits=serial.STOPBITS_ONE,
                timeout=1
            )

            print(f"  ✅ Terhubung ke {config.port}!")
            print(f"  ⏳ Menunggu card RFID... (Ctrl+C untuk berhenti)\n")

            buffer = ""

            while True:
                if ser.in_waiting > 0:
                    # Baca data dari serial port
                    raw = ser.read(ser.in_waiting)
                    try:
                        data = raw.decode('ascii', errors='ignore')
                    except:
                        data = raw.decode('utf-8', errors='ignore')

                    buffer += data

                    # Cek apakah ada newline (end of UID)
                    while '\n' in buffer or '\r' in buffer:
                        # Split by newline or carriage return
                        lines = buffer.replace('\r\n', '\n').replace('\r', '\n').split('\n')
                        for line in lines[:-1]:
                            uid = line.strip()
                            if uid:  # Hanya proses jika ada data
                                now = datetime.now().strftime('%H:%M:%S')
                                print(f"  [{now}] 📶 Card terdeteksi: {uid}")
                                send_to_api(uid, config.url)
                        buffer = lines[-1]  # Sisa data yang belum complete

                time.sleep(0.05)  # Small delay to prevent CPU overload

        except serial.SerialException as e:
            print(f"\n  ❌ Serial Error: {str(e)}")
            print(f"  🔄 Mencoba reconnect dalam 3 detik...")
            time.sleep(3)

        except KeyboardInterrupt:
            print(f"\n\n  🛑 RFID Reader dihentikan.")
            if 'ser' in locals() and ser.is_open:
                ser.close()
                print(f"  ✅ Port {config.port} ditutup.")
            sys.exit(0)

        except Exception as e:
            print(f"\n  ❌ Error: {str(e)}")
            print(f"  🔄 Mencoba reconnect dalam 3 detik...")
            time.sleep(3)


if __name__ == '__main__':
    main()
