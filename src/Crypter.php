<?php

declare(strict_types=1);

namespace AndKom\Bitcoin\Wallet;

/**
 * Class Crypter
 * @package AndKom\Bitcoin\Wallet
 */
class Crypter
{
    const WALLET_CRYPTO_KEY_SIZE = 32;
    const WALLET_CRYPTO_IV_SIZE = 16;
    const WALLET_CRYPTO_SALT_SIZE = 8;

    /**
     * @var null|string
     */
    protected $key;

    /**
     * @var null|string
     */
    protected $iv;

    /**
     * Crypter constructor.
     * @param string|null $key
     * @param string|null $iv
     * @throws Exception
     */
    public function __construct(string $key = null, string $iv = null)
    {
        if ($key !== null) {
            $this->setKey($key);
        }

        if ($iv !== null) {
            $this->setIv($iv);
        }
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getIv(): string
    {
        return $this->iv;
    }

    /**
     * @param string $key
     * @return Crypter
     * @throws Exception
     */
    public function setKey(string $key): self
    {
        if (strlen($key) != static::WALLET_CRYPTO_KEY_SIZE) {
            throw new Exception('Invalid key size.');
        }

        $this->key = $key;

        return $this;
    }

    /**
     * @param string $iv
     * @return Crypter
     * @throws Exception
     */
    public function setIv(string $iv): self
    {
        if (strlen($iv) != static::WALLET_CRYPTO_IV_SIZE) {
            throw new Exception('Invalid IV size.');
        }

        $this->iv = $iv;

        return $this;
    }

    /**
     * @param string $passphrase
     * @param string $salt
     * @param int $rounds
     * @param int $method
     * @return $this
     * @throws Exception
     */
    public function setKeyFromPassphrase(string $passphrase, string $salt, int $rounds, int $method)
    {
        if ($method != 0) {
            throw new Exception('Invalid derivation method.');
        }

        if ($rounds < 1) {
            throw new Exception('Too few derivation rounds.');
        }

        if (strlen($salt) != static::WALLET_CRYPTO_SALT_SIZE) {
            throw new Exception('Invalid salt size.');
        }

        $hash = $passphrase . $salt;

        for ($i = 0; $i < $rounds; $i++) {
            $hash = hash('SHA512', $hash, true);
        }

        $this->key = substr($hash, 0, static::WALLET_CRYPTO_KEY_SIZE);
        $this->iv = substr($hash, static::WALLET_CRYPTO_KEY_SIZE, static::WALLET_CRYPTO_IV_SIZE);

        return $this;
    }

    /**
     * @param string $decrypted
     * @return string
     * @throws Exception
     */
    public function encrypt(string $decrypted): string
    {
        if (!$this->key || !$this->iv) {
            throw new Exception('Empty encryption key or IV.');
        }

        $encrypted = openssl_encrypt(
            $decrypted,
            'AES-256-CBC',
            $this->key,
            OPENSSL_RAW_DATA,
            $this->iv);

        if ($encrypted === false) {
            throw new Exception('Unable to encrypt.');
        }

        return $encrypted;
    }

    /**
     * @param string $encrypted
     * @return string
     * @throws Exception
     */
    public function decrypt(string $encrypted): string
    {
        if (!$this->key || !$this->iv) {
            throw new Exception('Empty encryption key or IV.');
        }

        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $this->key,
            OPENSSL_RAW_DATA,
            $this->iv);

        if ($decrypted === false) {
            throw new Exception('Unable to decrypt.');
        }

        return $decrypted;
    }

    /**
     * @param string $string
     * @return string
     */
    public static function hash(string $string)
    {
        return hash('SHA256', hash('SHA256', $string, true), true);
    }
}