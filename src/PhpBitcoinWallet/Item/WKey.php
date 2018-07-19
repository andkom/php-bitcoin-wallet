<?php

declare(strict_types=1);

namespace AndKom\PhpBitcoinWallet\Item;

/**
 * Class WKey
 * @package AndKom\PhpBitcoinWallet\Item
 */
class WKey extends Key
{
    /**
     * @var array
     */
    protected $attributes;

    /**
     * WKey constructor.
     * @param string $public
     * @param string $private
     * @param KeyMeta|null $meta
     * @param array $attributes
     */
    public function __construct(string $public, string $private, KeyMeta $meta = null, array $attributes = [])
    {
        $this->attributes = $attributes;

        parent::__construct($public, $private, $meta);
    }
}