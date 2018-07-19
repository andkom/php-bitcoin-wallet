<?php

declare(strict_types=1);

namespace AndKom\PhpBitcoinWallet;

// determine machine byte-order
define('BIG_ENDIAN', pack('L', 1) === pack('N', 1));

/**
 * Class BCDataStream
 * @package AndKom\PhpBitcoinWallet
 */
class BCDataStream
{
    const BO_MACHINE = 0;
    const BO_LE = 1;
    const BO_BE = 2;

    /**
     * @var string
     */
    protected $buffer;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var int
     */
    protected $byteOrder;

    /**
     * BCDataStream constructor.
     * @param string $buffer
     * @param int $position
     * @param int $byteOrder
     */
    public function __construct(string $buffer = '', int $position = 0, int $byteOrder = self::BO_LE)
    {
        $this->setBuffer($buffer);
        $this->setPosition($position);
        $this->setByteOrder($byteOrder);
    }

    /**
     * @return BCDataStream
     */
    public function clear(): self
    {
        $this->buffer = '';
        $this->position = 0;

        return $this;
    }

    /**
     * @return string
     */
    public function getBuffer(): string
    {
        return $this->buffer;
    }

    /**
     * @param string $buffer
     * @return BCDataStream
     */
    public function setBuffer(string $buffer): self
    {
        $this->buffer = $buffer;
        $this->position = 0;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getBuffer();
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return BCDataStream
     */
    public function setPosition(int $position): self
    {
        if ($this->position < 0) {
            throw new \InvalidArgumentException('Negative position.');
        }

        if ($this->position > strlen($this->buffer)) {
            throw new \InvalidArgumentException('Position is greater then buffer length.');
        }

        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getByteOrder(): int
    {
        return $this->byteOrder;
    }

    /**
     * @param int $byteOrder
     * @return BCDataStream
     */
    public function setByteOrder(int $byteOrder): self
    {
        $this->byteOrder = $byteOrder;

        return $this;
    }

    /**
     * @param int $size
     * @return string
     */
    public function read(int $size): string
    {
        if ($this->position + $size > strlen($this->buffer)) {
            throw new \InvalidArgumentException("Not enough buffer to read $size byte(s).");
        }

        $bytes = substr($this->buffer, $this->position, $size);

        $this->position += strlen($bytes);

        return $bytes;
    }

    /**
     * @param string $bytes
     * @return BCDataStream
     */
    public function write(string $bytes): self
    {
        $this->buffer .= $bytes;

        return $this;
    }

    /**
     * @param string $format
     * @return int
     */
    public function readNum(string $format): int
    {
        $size = strlen(pack($format, 1));
        $bytes = $this->read($size);

        if ($this->byteOrder != static::BO_MACHINE && ($this->byteOrder == static::BO_BE) != BIG_ENDIAN) {
            $bytes = strrev($bytes);
        }

        list(, $num) = unpack($format, $bytes);

        return $num;
    }

    /**
     * @param string $format
     * @param int $num
     * @return BCDataStream
     */
    public function writeNum(string $format, int $num): self
    {
        $bytes = pack($format, $num);

        if ($this->byteOrder != static::BO_MACHINE && ($this->byteOrder == static::BO_BE) != BIG_ENDIAN) {
            $bytes = strrev($bytes);
        }

        return $this->write($bytes);
    }

    /**
     * Strings are encoded depending on length:
     * 0 to 252 :  1-byte-length followed by bytes (if any)
     * 253 to 65,535 : byte'253' 2-byte-length followed by bytes
     * 65,536 to 4,294,967,295 : byte '254' 4-byte-length followed by bytes
     * greater than 4,294,967,295 : byte '255' 8-byte-length followed by bytes of string.
     * @return int
     */
    public function readCompactSize(): int
    {
        $size = ord($this->buffer[$this->position]);
        $this->position++;

        if ($size == 253) {
            $size = $this->readNum('S');
        } elseif ($size == 254) {
            $size = $this->readNum('L');
        } elseif ($size == 255) {
            $size = $this->readNum('Q');
        }

        return $size;
    }

    /**
     * @param int $size
     * @return BCDataStream
     */
    public function writeCompactSize(int $size): self
    {
        if ($size < 0) {
            throw new \InvalidArgumentException('Size is zero.');
        } elseif ($size < 253) {
            return $this->write(chr($size));
        } elseif ($size < 2 ** 16) {
            return $this->write("\xfd")->writeNum('S', $size);
        } elseif ($size < 2 ** 32) {
            return $this->write("\xfe")->writeNum('L', $size);
        } elseif ($size < 2 ** 64) {
            return write("\xff")->writeNum('Q', $size);
        }
    }

    /**
     * @return string
     */
    public function readString(): string
    {
        return $this->read($this->readCompactSize());
    }

    /**
     * @param string $string
     * @return BCDataStream
     */
    public function writeString(string $string): self
    {
        return $this->writeCompactSize(strlen($string))->write($string);
    }

    /**
     * @return bool
     */
    public function readBoolean(): bool
    {
        return $this->read(1) != chr(0);
    }

    /**
     * @param bool $boolean
     * @return BCDataStream
     */
    public function writeBoolean(bool $boolean): self
    {
        return $this->write($boolean ? "\1" : "\0");
    }

    /**
     * @return int
     */
    public function readInt16(): int
    {
        return $this->readNum('s');
    }

    /**
     * @param int $int
     * @return BCDataStream
     */
    public function writeInt16(int $int): self
    {
        return $this->writeNum('s', $int);
    }

    /**
     * @return int
     */
    public function readUInt16(): int
    {
        return $this->readNum('S');
    }

    /**
     * @param int $int
     * @return BCDataStream
     */
    public function writeUInt16(int $int): self
    {
        return $this->writeNum('S', $int);
    }

    /**
     * @return int
     */
    public function readInt32(): int
    {
        return $this->readNum('l');
    }

    /**
     * @param int $int
     * @return BCDataStream
     */
    public function writeInt32(int $int): self
    {
        return $this->writeNum('l', $int);
    }

    /**
     * @return int
     */
    public function readUInt32(): int
    {
        return $this->readNum('L');
    }

    /**
     * @param int $int
     * @return BCDataStream
     */
    public function writeUInt32(int $int): self
    {
        return $this->writeNum('L', $int);
    }

    /**
     * @return int
     */
    public function readInt64(): int
    {
        return $this->readNum('q');
    }

    /**
     * @param int $int
     * @return BCDataStream
     */
    public function writeInt64(int $int): self
    {
        return $this->writeNum('q', $int);
    }

    /**
     * @return int
     */
    public function readUInt64(): int
    {
        return $this->readNum('Q');
    }

    /**
     * @param int $int
     * @return BCDataStream
     */
    public function writeUInt64(int $int): self
    {
        return $this->writeNum('Q', $int);
    }
}