import sys
import time
from response import hex_readable, Response
from transport import TcpTransport
from reader import Reader

READER_IP = '192.168.1.190'
READER_PORT = 6000

print("Connecting to TCP...")
try:
    transport = TcpTransport(READER_IP, READER_PORT)
    reader = Reader(transport)
    
    print("Checking Work Mode...")
    mode = reader.work_mode()
    print(mode)
    
    print("\nReading in Active Mode (waiting for 10 seconds)...")
    start = time.time()
    while time.time() - start < 10:
        raw = transport.read_frame()
        if raw:
            print(f"RAW RECEIVED: {hex_readable(raw)}")
            try:
                resp = Response(raw)
                print(resp)
            except Exception as e:
                print(f"Parse error: {e}")
        time.sleep(0.1)
        
    print("Closing connection...")
    reader.close()
    
except Exception as e:
    print(f"Error: {e}")
