<?php

declare(strict_types=1);

namespace AndKom\Bitcoin\Wallet\Tests;

use AndKom\Bitcoin\Wallet\Crypter;
use PHPUnit\Framework\TestCase;

class CrypterTest extends TestCase
{
    protected $key = '5a02df5f54af29c84b7065c6b144481c89a6c62292638fcad4e0af2532bdcd53';
    protected $iv = '66a9a844d2d3f63f8dfc844ca185ded8';
    protected $testEncrypted = 'a842da145f5bb4402378a1aeaca5bf71';

    public function testEncrypt()
    {
        $crypter = new Crypter(hex2bin($this->key), hex2bin($this->iv));
        $this->assertEquals($crypter->encrypt('test'), hex2bin($this->testEncrypted));
    }

    public function testDecrypt()
    {
        $crypter = new Crypter(hex2bin($this->key), hex2bin($this->iv));
        $this->assertEquals($crypter->decrypt(hex2bin($this->testEncrypted)), 'test');
    }

    public function testSetKeyFromPassphrase()
    {
        $crypter = new Crypter();
        $crypter->setKeyFromPassphrase('test', hex2bin('3d9e6ee4916db8da'), 1000, 0);
        $this->assertEquals($crypter->getKey(), hex2bin('a33cccd00307e900485425f6ce78e31a34fa467cd45d26fd493b1b88d4da70e8'));
        $this->assertEquals($crypter->getIv(), hex2bin('7e19411b00669f099968d1a5663a91e2'));
    }

    public function testHash()
    {
        $this->assertEquals(Crypter::hash('test'), hex2bin('954d5a49fd70d9b8bcdb35d252267829957f7ef7fa6c74f88419bdc5e82209f4'));
    }
}