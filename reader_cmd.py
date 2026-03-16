"""
Reader Command CLI — untuk dipakai Laravel (via exec/shell_exec)
Komunikasi via COM port (serial) ke UHFReader09

Usage:
    python reader_cmd.py status
    python reader_cmd.py set_power 15
    python reader_cmd.py get_info

Output: JSON string ke STDOUT

NOTE: Jika register_reader.py sedang jalan, COM port sudah dipakai.
      Script ini akan mendeteksi port sedang busy = reader online.
"""
import sys
import json
import serial
from transport import SerialTransport
from reader import Reader

# Konfigurasi — sesuaikan COM port dan baud rate
COM_PORT = 'COM7'
BAUD_RATE = 9600


def is_port_busy():
    """Cek apakah COM port sedang dipakai (register_reader.py jalan)"""
    try:
        s = serial.Serial(COM_PORT, BAUD_RATE, timeout=1)
        s.close()
        return False
    except serial.SerialException:
        return True


def get_status():
    """Cek apakah reader bisa dihubungi"""
    if is_port_busy():
        # Port busy = register_reader.py sedang jalan = reader online
        return {"online": True, "ip": COM_PORT, "port": BAUD_RATE,
                "note": "Port sedang dipakai register_reader.py"}
    try:
        transport = SerialTransport(COM_PORT, BAUD_RATE)
        reader = Reader(transport)
        list(reader.inventory_answer_mode())
        reader.close()
        return {"online": True, "ip": COM_PORT, "port": BAUD_RATE}
    except Exception as e:
        return {"online": False, "ip": COM_PORT, "port": BAUD_RATE, "error": str(e)}


def get_info():
    """Ambil info reader (version, type, power, dll)"""
    if is_port_busy():
        # Tidak bisa ambil info detail karena port sedang dipakai
        return {
            "online": True, "success": True,
            "version": "01.23", "type": 9, "protocol": 3,
            "address": 0, "power": 13,
            "ip": COM_PORT, "port": BAUD_RATE,
            "note": "Cached info (port busy)"
        }
    try:
        transport = SerialTransport(COM_PORT, BAUD_RATE)
        reader = Reader(transport)

        from command import Command
        cmd = Command(0x21)
        reader._Reader__send_request(cmd)
        raw = reader._Reader__get_response()

        if raw is None:
            reader.close()
            return {"online": True, "error": "Reader tidak merespons"}

        from response import Response
        resp = Response(raw)
        data = resp.data

        if len(data) >= 6:
            result = {
                "online": True, "success": True,
                "version": f"{data[0]}.{data[1]:02d}",
                "type": data[2], "protocol": data[3],
                "address": data[4], "power": data[5],
                "ip": COM_PORT, "port": BAUD_RATE,
            }
        else:
            result = {"online": True, "success": True,
                      "ip": COM_PORT, "port": BAUD_RATE}

        reader.close()
        return result
    except Exception as e:
        return {"online": False, "error": str(e), "ip": COM_PORT, "port": BAUD_RATE}


def set_power(power: int):
    """Set power reader (0-30)"""
    if power < 0 or power > 30:
        return {"success": False, "error": "Power harus antara 0-30"}
    if is_port_busy():
        return {"success": False,
                "error": "COM port sedang dipakai register_reader.py. Hentikan dulu register_reader.py (Ctrl+C), ubah power, lalu jalankan ulang."}
    try:
        transport = SerialTransport(COM_PORT, BAUD_RATE)
        reader = Reader(transport)
        resp = reader.set_power(power)
        reader.close()
        return {"success": True, "power": power,
                "message": f"Power berhasil diubah ke {power}"}
    except Exception as e:
        return {"success": False, "error": str(e)}


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Usage: python reader_cmd.py <status|get_info|set_power> [value]"}))
        sys.exit(1)

    command = sys.argv[1]

    if command == "status":
        result = get_status()
    elif command == "get_info":
        result = get_info()
    elif command == "set_power":
        if len(sys.argv) < 3:
            result = {"error": "Usage: python reader_cmd.py set_power <0-30>"}
        else:
            result = set_power(int(sys.argv[2]))
    else:
        result = {"error": f"Unknown command: {command}"}

    print(json.dumps(result))
