<?php

declare(strict_types=1);

namespace AndKom\PhpBitcoinWallet\Tests;

use AndKom\PhpBitcoinWallet\BCDataStream;
use PHPUnit\Framework\TestCase;

class BCDataStreamTest extends TestCase
{
    public function testFunctions()
    {
        $this->assertEquals((new BCDataStream())->getBuffer(), '');
        $this->assertEquals((new BCDataStream())->getPosition(), 0);
        $this->assertEquals((new BCDataStream())->setBuffer('test')->getBuffer(), 'test');
        $this->assertEquals((new BCDataStream('test'))->setPosition(1)->getPosition(), 1);
        $this->assertEquals((new BCDataStream('test'))->clear()->getBuffer(), '');
        $this->assertEquals((new BCDataStream('test')), 'test');
        $this->assertEquals((new BCDataStream("testtest"))->setPosition(2)->read(4), 'stte');
    }

    public function testRead()
    {
        $this->assertEquals((new BCDataStream("\x04test"))->readString(), 'test');
        $this->assertEquals((new BCDataStream("\0"))->readBoolean(), false);
        $this->assertEquals((new BCDataStream("\1"))->readBoolean(), true);
        $this->assertEquals((new BCDataStream("\x01\x00"))->readInt16(), 1);
        $this->assertEquals((new BCDataStream("\x01\x00"))->readUInt16(), 1);
        $this->assertEquals((new BCDataStream("\x01\x00\x00\x00"))->readInt32(), 1);
        $this->assertEquals((new BCDataStream("\x01\x00\x00\x00"))->readUInt32(), 1);
        $this->assertEquals((new BCDataStream("\x01\x00\x00\x00\x00\x00\x00\x00"))->readInt64(), 1);
        $this->assertEquals((new BCDataStream("\x01\x00\x00\x00\x00\x00\x00\x00"))->readUInt64(), 1);
        $this->assertEquals((new BCDataStream("\x00\x01"))->setByteOrder(BCDataStream::BO_BE)->readInt16(), 1);
        $this->assertEquals((new BCDataStream("\x00\x01"))->setByteOrder(BCDataStream::BO_BE)->readUInt16(), 1);
        $this->assertEquals((new BCDataStream("\x00\x00\x00\x01"))->setByteOrder(BCDataStream::BO_BE)->readInt32(), 1);
        $this->assertEquals((new BCDataStream("\x00\x00\x00\x01"))->setByteOrder(BCDataStream::BO_BE)->readUInt32(), 1);
        $this->assertEquals((new BCDataStream("\x00\x00\x00\x00\x00\x00\x00\x01"))->setByteOrder(BCDataStream::BO_BE)->readInt64(), 1);
        $this->assertEquals((new BCDataStream("\x00\x00\x00\x00\x00\x00\x00\x01"))->setByteOrder(BCDataStream::BO_BE)->readUInt64(), 1);
    }

    public function testWrite()
    {
        $this->assertEquals((new BCDataStream())->writeString('test')->getBuffer(), "\x04test");
        $this->assertEquals((new BCDataStream())->writeBoolean(false)->getBuffer(), "\0");
        $this->assertEquals((new BCDataStream())->writeBoolean(true)->getBuffer(), "\1");
        $this->assertEquals((new BCDataStream())->writeInt16(1)->getBuffer(), "\x01\x00");
        $this->assertEquals((new BCDataStream())->writeUInt16(1)->getBuffer(), "\x01\x00");
        $this->assertEquals((new BCDataStream())->writeInt32(1)->getBuffer(), "\x01\x00\x00\x00");
        $this->assertEquals((new BCDataStream())->writeUInt32(1)->getBuffer(), "\x01\x00\x00\x00");
        $this->assertEquals((new BCDataStream())->writeInt64(1)->getBuffer(), "\x01\x00\x00\x00\x00\x00\x00\x00");
        $this->assertEquals((new BCDataStream())->writeUInt64(1)->getBuffer(), "\x01\x00\x00\x00\x00\x00\x00\x00");
        $this->assertEquals((new BCDataStream())->setByteOrder(BCDataStream::BO_BE)->writeInt16(1)->getBuffer(), "\x00\x01");
        $this->assertEquals((new BCDataStream())->setByteOrder(BCDataStream::BO_BE)->writeUInt16(1)->getBuffer(), "\x00\x01");
        $this->assertEquals((new BCDataStream())->setByteOrder(BCDataStream::BO_BE)->writeInt32(1)->getBuffer(), "\x00\x00\x00\x01");
        $this->assertEquals((new BCDataStream())->setByteOrder(BCDataStream::BO_BE)->writeUInt32(1)->getBuffer(), "\x00\x00\x00\x01");
        $this->assertEquals((new BCDataStream())->setByteOrder(BCDataStream::BO_BE)->writeInt64(1)->getBuffer(), "\x00\x00\x00\x00\x00\x00\x00\x01");
        $this->assertEquals((new BCDataStream())->setByteOrder(BCDataStream::BO_BE)->writeUInt64(1)->getBuffer(), "\x00\x00\x00\x00\x00\x00\x00\x01");
    }
}