<?php

declare(strict_types=1);

namespace AndKom\Bitcoin\Wallet\Item;

use AndKom\Bitcoin\Wallet\Crypter;
use AndKom\Bitcoin\Wallet\Exception;

/**
 * Class EncryptedKey
 * @package AndKom\Bitcoin\Wallet\Item
 */
class EncryptedKey extends Key
{
    /**
     * @return bool
     */
    public function isEncrypted(): bool
    {
        return !$this->private;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getPrivateKey(): string
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
        return $this->secret;
    }

    /**
     * @return string
     */
    public function getIv(): string
    {
        $hash = Crypter::hash($this->getPublicKey());
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
        $this->private = $crypter->decrypt($this->getEncryptedPrivateKey());

        return $this;
    }
}