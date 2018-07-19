<?php

declare(strict_types=1);

namespace AndKom\PhpBitcoinWallet\Tests;

use AndKom\PhpBitcoinWallet\Wallet;
use PHPUnit\Framework\TestCase;

class WalletTest extends TestCase
{
    public function testParse()
    {
        $wallet = new Wallet();
        $wallet->read(__DIR__ . '/data/wallet.dat');

        $key = array_first($wallet->getKeys());

        $this->assertEquals($key->getPrivateKey()->toWif(), 'L1uaD1GSyvL78gRkBgMggLSUYkMrULFVGeS9wTxhLcBJN83HYRF3');
        $this->assertEquals($key->getPublicKey()->getAddress()->getAddress(), '16nxVXYNE8jyBjNtEpVJ2GA5mhJczsrQVa');
    }

    public function testMasterKey()
    {
        $wallet = new Wallet();
        $wallet->read(__DIR__ . '/data/wallet_encrypted.dat');

        $key = array_last($wallet->getKeys());
        $mk = $wallet->getMasterKey();

        $this->assertEquals(bin2hex($mk->getEncryptedKey()), '80bb7a5985fd80e71c4b7f1601ce8fd7681a195c345695c6e87396eb7f8aefbf4e098ed009a42a173bd6db863c24d464');
        $this->assertEquals(bin2hex($mk->getSalt()), '98313fb978e6ef49');
        $this->assertEquals($mk->getDerivationMethod(), 0);
        $this->assertEquals($mk->getDerivationIterations(), 196349);
        $this->assertEquals($mk->getHash($key), '$bitcoin$64$681a195c345695c6e87396eb7f8aefbf4e098ed009a42a173bd6db863c24d464$16$98313fb978e6ef49$196349$96$efe4244d839af470418ee08b278ffd20510dcd105bd1aa3de016bf59c35f99b8537222a0e4a8ea1db2b6b795de697785$66$03f4c3e512b84d950cf7568966a89c9048526076a2654c907a43db8fb8f38db508');
    }

    public function testDecrypt()
    {
        $wallet = new Wallet();
        $wallet->read(__DIR__ . '/data/wallet_encrypted.dat');
        $wallet->decrypt('test');

        $key = array_first($wallet->getKeys());

        $this->assertEquals($key->getPrivateKey()->toWif(), 'Kz1MJgnRAmUoeWq6gVwEmeCy1ykKPjNbDK9bcDbCUMipSLMKnwrm');
        $this->assertEquals($key->getPublicKey()->getAddress()->getAddress(), '1Gh5nEM4gjkWZYXFWXzMbquwzSDgiBF1dE');
    }

    public function testVersion()
    {
        $wallet = new Wallet();
        $wallet->read(__DIR__ . '/data/wallet.dat');

        $this->assertEquals($wallet->getAttributes()['version'], 160100);
        $this->assertEquals($wallet->getAttributes()['minversion'], 159900);
    }
}