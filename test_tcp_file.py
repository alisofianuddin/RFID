import sys
import time
from response import hex_readable, Response
from transport import TcpTransport
from reader import Reader

READER_IP = '192.168.1.190'
READER_PORT = 6000

with open("tcp_debug.txt", "w") as f:
    f.write("Connecting to TCP...\n")
    try:
        transport = TcpTransport(READER_IP, READER_PORT)
        reader = Reader(transport)
        
        f.write("Checking Work Mode...\n")
        mode = reader.work_mode()
        f.write(repr(str(mode)) + "\n")
        
        f.write("\nReading raw bytes for 5 seconds...\n")
        start = time.time()
        while time.time() - start < 5:
            raw = transport.read_frame()
            if raw:
                f.write(f"RAW: {hex_readable(raw)}\n")
                try:
                    resp = Response(raw)
                    f.write(f"Parsed CMD {resp.command:02X} STATUS {resp.status:02X} DATA {hex_readable(resp.data)}\n")
                except Exception as e:
                    f.write(f"ERR: {e}\n")
            time.sleep(0.1)
            
        f.write("Done.\n")
        reader.close()
        
    except Exception as e:
        f.write(f"Fatal Error: {e}\n")
