<?php

declare(strict_types=1);

namespace AndKom\Bitcoin\Wallet\Item;

/**
 * Class Key
 * @package AndKom\Bitcoin\Wallet\Item
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
    protected $secret;

    /**
     * @var string
     */
    protected $private;

    /**
     * @var KeyMeta
     */
    protected $meta;

    /**
     * Key constructor.
     * @param string $public
     * @param string $secret
     * @param KeyMeta $meta
     */
    public function __construct(string $public, string $secret, KeyMeta $meta = null)
    {
        $this->public = $public;
        $this->secret = $secret;
        $this->meta = $meta;
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
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->public;
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        if (!$this->private) {
            $this->private = $this->parseSecret($this->secret);
        }

        return $this->private;
    }
}