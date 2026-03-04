from abc import ABC, abstractmethod
from socket import socket, AF_INET, SOCK_STREAM, timeout as SocketTimeout
from typing import TypeVar
import serial

T = TypeVar('T', bound='Parent')


class Transport(ABC):
    @abstractmethod
    def read_bytes(self, length: int) -> bytes:
        raise NotImplementedError

    @abstractmethod
    def write_bytes(self, buffer: bytes) -> None:
        raise NotImplementedError

    def read_frame(self) -> bytes | None:
        try:
            length_bytes = self.read_bytes(1)
        except (TimeoutError, SocketTimeout, serial.SerialTimeoutException):
            return None
        if not length_bytes:
            return None
        frame_length = length_bytes[0]
        remaining = self.read_bytes(frame_length)
        if not remaining:
            return None
        data = bytearray(length_bytes) + bytearray(remaining)
        return data

    @abstractmethod
    def close(self) -> None:
        raise NotImplementedError


class TcpTransport(Transport):
    def __init__(self, ip_address: str, port: int, timeout: int = 3) -> None:
        self.socket = socket(AF_INET, SOCK_STREAM)
        self.socket.settimeout(timeout)
        self.socket.connect((ip_address, port))
        print(f"[TCP] Terhubung ke {ip_address}:{port}")

    def read_bytes(self, length: int) -> bytes:
        """Baca persis sejumlah byte dari socket (handle partial reads)"""
        buffer = bytearray()
        while len(buffer) < length:
            chunk = self.socket.recv(length - len(buffer))
            if not chunk:
                break
            buffer.extend(chunk)
        return bytes(buffer)

    def write_bytes(self, buffer: bytes) -> None:
        self.socket.sendall(buffer)

    def close(self) -> None:
        self.socket.close()
        print("[TCP] Koneksi ditutup")


class SerialTransport(Transport):
    def __init__(self, serial_port: str, baud_rate: int, timeout: int = 1) -> None:
        self.serial = serial.Serial(serial_port, baud_rate,
                                    timeout=timeout, write_timeout=timeout)
        print(f"[Serial] Terhubung ke {serial_port} @ {baud_rate}bps")

    def read_bytes(self, length: int) -> bytes:
        return self.serial.read(length)

    def write_bytes(self, buffer: bytes) -> None:
        self.serial.write(buffer)

    def close(self) -> None:
        self.serial.close()
        print("[Serial] Koneksi ditutup")
