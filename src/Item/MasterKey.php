<?php

declare(strict_types=1);

namespace AndKom\Bitcoin\Wallet\Item;

use AndKom\Bitcoin\Wallet\Crypter;

/**
 * Class MasterKey
 * @package AndKom\Bitcoin\Wallet\Item
 */
class MasterKey
{
    /**
     * @var array
     */
    protected $attributes;

    /**
     * MasterKey constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getEncryptedKey(): string
    {
        return $this->attributes['encrypted_key'];
    }

    /**
     * @return string
     */
    public function getSalt(): string
    {
        return $this->attributes['salt'];
    }

    /**
     * @return int
     */
    public function getDerivationMethod(): int
    {
        return $this->attributes['nDerivationMethod'];
    }

    /**
     * @return int
     */
    public function getDerivationIterations(): int
    {
        return $this->attributes['nDerivationIterations'];
    }

    /**
     * Returns master key hash for hashcat.
     * @param EncryptedKey $encryptedKey
     * @return string
     * @throws \AndKom\Bitcoin\Wallet\Exception
     */
    public function getHash(EncryptedKey $encryptedKey): string
    {
        $encrypted = bin2hex($encryptedKey->getEncryptedPrivateKey());
        $public = bin2hex($encryptedKey->getPublicKey());
        $master = substr(bin2hex($this->getEncryptedKey()), -64); // last two aes blocks should be enough
        $salt = bin2hex($this->getSalt());

        return sprintf('$bitcoin$%d$%s$%d$%s$%d$%d$%s$%d$%s',
            strlen($master),
            $master,
            strlen($salt),
            $salt,
            $this->getDerivationIterations(),
            strlen($encrypted),
            $encrypted,
            strlen($public),
            $public);
    }

    /**
     * @param string $passphrase
     * @return string
     * @throws \AndKom\Bitcoin\Wallet\Exception
     */
    public function decrypt(string $passphrase): string
    {
        $crypter = new Crypter();

        $crypter->setKeyFromPassphrase(
            $passphrase,
            $this->getSalt(),
            $this->getDerivationIterations(),
            $this->getDerivationMethod());

        return $crypter->decrypt($this->getEncryptedKey());
    }
}