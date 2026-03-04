def calculate_checksum(data: bytes) -> bytearray:
    value = 0xFFFF
    for d in data:
        value ^= d
        for _ in range(8):
            value = (value >> 1) ^ 0x8408 if value & 0x0001 else (value >> 1)
    crc_msb = value >> 0x08
    crc_lsb = value & 0xFF
    return bytearray([crc_lsb, crc_msb])


def hex_readable(data: bytes | int, bytes_separator: str = " ") -> str:
    if isinstance(data, int):
        return "{:02X}".format(data)
    return bytes_separator.join("{:02X}".format(x) for x in data)
