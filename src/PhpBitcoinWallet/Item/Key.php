<?php

declare(strict_types=1);

namespace AndKom\PhpBitcoinWallet\Item;

use AndKom\PhpBitcoinWallet\Exception;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PrivateKeyInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PublicKeyInterface;
use BitWasp\Bitcoin\Key\PrivateKeyFactory;
use BitWasp\Bitcoin\Key\PublicKeyFactory;

/**
 * Class Key
 * @package AndKom\PhpBitcoinWallet\Item
 */
class Key
{
    const UNCOMPRESSED_PRIVATE_KEY_SIZE = 279;

    /**
     * @var string
     */
    protected $public;

    /**
     * @var string
     */
    protected $private;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var KeyMeta
     */
    protected $meta;

    /**
     * Key constructor.
     * @param string $public
     * @param string $private
     * @param KeyMeta $meta
     */
    public function __construct(string $public, string $private, KeyMeta $meta = null)
    {
        $this->public = $public;
        $this->private = $private;
        $this->meta = $meta;
        $this->secret = $this->parseSecret($private);
    }

    /**
     * Parse private key secret from EC private key (DER format).
     * @todo better implementation
     * @param string $data
     * @return string
     */
    protected function parseSecret(string $data): string
    {
        if (strlen($data) == static::UNCOMPRESSED_PRIVATE_KEY_SIZE) {
            $private = substr($data, 9, 32);
        } else {
            $private = substr($data, 8, 32);
        }

        return $private;
    }

    /**
     * @return PublicKeyInterface
     * @throws Exception
     */
    public function getPublicKey(): PublicKeyInterface
    {
        $hex = bin2hex($this->public);

        try {
            $publicKey = PublicKeyFactory::fromHex($hex);
        } catch (\Exception $exception) {
            throw new Exception('Unable to decode public key: ' . $exception->getMessage());
        }

        return $publicKey;
    }

    /**
     * @return PrivateKeyInterface
     * @throws Exception
     */
    public function getPrivateKey(): PrivateKeyInterface
    {
        $hex = bin2hex($this->secret);

        try {
            $privateKey = PrivateKeyFactory::fromHex($hex, $this->getPublicKey()->isCompressed());
        } catch (\Exception $exception) {
            throw new Exception('Unable to decode private key: ' . $exception->getMessage());
        }

        return $privateKey;
    }
}