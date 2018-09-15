<?php

declare(strict_types=1);

namespace AndKom\Bitcoin\Wallet\Item;

use AndKom\Bitcoin\Wallet\Crypter;
use AndKom\Bitcoin\Wallet\Exception;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PrivateKeyInterface;

/**
 * Class EncryptedKey
 * @package AndKom\Bitcoin\Wallet\Item
 */
class EncryptedKey extends Key
{
    /**
     * @var string
     */
    protected $encrypted;

    /**
     * EncryptedKey constructor.
     * @param string $public
     * @param string $encrypted
     * @param KeyMeta|null $meta
     */
    public function __construct(string $public, string $encrypted, KeyMeta $meta = null)
    {
        $this->public = $public;
        $this->encrypted = $encrypted;
        $this->meta = $meta;
    }

    /**
     * @return bool
     */
    public function isEncrypted(): bool
    {
        return !$this->secret;
    }

    /**
     * @return PrivateKeyInterface
     * @throws Exception
     */
    public function getPrivateKey(): PrivateKeyInterface
    {
        if ($this->isEncrypted()) {
            throw new Exception('Private key is encrypted.');
        }

        return parent::getPrivateKey();
    }

    /**
     * @return string
     */
    public function getEncryptedPrivateKey(): string
    {
        return $this->encrypted;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getIv(): string
    {
        $hash = Crypter::hash($this->getPublicKey()->getBinary());
        $iv = substr($hash, 0, Crypter::WALLET_CRYPTO_IV_SIZE);

        return $iv;
    }

    /**
     * @param string $masterKey
     * @return EncryptedKey
     * @throws Exception
     */
    public function decrypt(string $masterKey): self
    {
        $crypter = new Crypter($masterKey, $this->getIv());
        $this->secret = $crypter->decrypt($this->getEncryptedPrivateKey());

        return $this;
    }
}