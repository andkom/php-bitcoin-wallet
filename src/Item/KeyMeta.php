<?php

declare(strict_types=1);

namespace AndKom\Bitcoin\Wallet\Item;

use AndKom\Bitcoin\Wallet\Exception;

/**
 * Class KeyMeta
 * @package AndKom\Bitcoin\Wallet\Item
 */
class KeyMeta
{
    const VERSION_BASIC = 1;
    const VERSION_WITH_HDDATA = 10;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * KeyMeta constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->attributes['nVersion'];
    }

    /**
     * @return int
     */
    public function getCreateTime(): int
    {
        return $this->attributes['nCreateTime'];
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getHdKeyPath(): string
    {
        if (isset($this->attributes['hdKeyPath'])) {
            throw new Exception('hdKeyPath attribute not found.');
        }

        return $this->attributes['hdKeyPath'];
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getHdMasterKeyId(): string
    {
        if (isset($this->attributes['hdMasterKeyId'])) {
            throw new Exception('hdKeyPath attribute not found.');
        }

        return $this->attributes['hdMasterKeyId'];
    }
}