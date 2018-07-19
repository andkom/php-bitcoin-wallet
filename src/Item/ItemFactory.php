<?php

declare(strict_types=1);

namespace AndKom\PhpBitcoinWallet\Item;

use AndKom\PhpBerkeleyDb\Adapter\AdapterInterface;
use AndKom\PhpBitcoinWallet\BCDataStream;

/**
 * Class ItemFactory
 * @package AndKom\PhpBitcoinWallet\Item
 */
class ItemFactory
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * ItemFactory constructor.
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param BCDataStream $kds
     * @param BCDataStream $vds
     * @return Key
     * @throws \AndKom\PhpBerkeleyDb\Exception
     */
    public function createKey(BCDataStream $kds, BCDataStream $vds): Key
    {
        $public = $kds->readString();
        $private = $vds->readString();

        return new Key($public, $private, $this->getKeyMeta($public));
    }

    /**
     * @param BCDataStream $kds
     * @param BCDataStream $vds
     * @return EncryptedKey
     * @throws \AndKom\PhpBerkeleyDb\Exception
     */
    public function createEncryptedKey(BCDataStream $kds, BCDataStream $vds): EncryptedKey
    {
        $public = $kds->readString();
        $encrypted = $vds->readString();

        return new EncryptedKey($public, $encrypted, $this->getKeyMeta($public));
    }

    /**
     * @param BCDataStream $kds
     * @param BCDataStream $vds
     * @return WKey
     * @throws \AndKom\PhpBerkeleyDb\Exception
     */
    public function createWKey(BCDataStream $kds, BCDataStream $vds): WKey
    {
        $public = $kds->readString();
        $private = $vds->readString();

        return new WKey($public, $private, $this->getKeyMeta($public), [
            'created' => $kds->readInt64(),
            'expires' => $kds->readInt64(),
            'comment' => $kds->readString(),
        ]);
    }

    /**
     * @param BCDataStream $kds
     * @param BCDataStream $vds
     * @return MasterKey
     */
    public function createMasterKey(BCDataStream $kds, BCDataStream $vds): MasterKey
    {
        return new MasterKey([
            'nId' => $kds->readUInt32(),
            'encrypted_key' => $vds->readString(),
            'salt' => $vds->readString(),
            'nDerivationMethod' => $vds->readUInt32(),
            'nDerivationIterations' => $vds->readUInt32(),
            'otherParams' => $vds->readString(),
        ]);
    }

    /**
     * @param BCDataStream $vds
     * @return KeyMeta
     */
    public function createKeyMeta(BCDataStream $vds): KeyMeta
    {
        $attributes = [
            'nVersion' => $version = $vds->readInt32(),
            'nCreateTime' => $vds->readInt64(),
        ];

        if ($version == KeyMeta::VERSION_WITH_HDDATA) {
            $attributes['hdKeyPath'] = $vds->readString();
            $attributes['hdMasterKeyId'] = $vds->read(20);
        }

        return new KeyMeta($attributes);
    }

    /**
     * @param string $public
     * @return KeyMeta|null
     * @throws \AndKom\PhpBerkeleyDb\Exception
     */
    public function getKeyMeta(string $public): ?KeyMeta
    {
        $key = (new BCDataStream())
            ->writeString('keymeta')
            ->writeString($public)
            ->getBuffer();

        $value = $this->adapter->get($key);

        if (!$value) {
            return null;
        }

        return $this->createKeyMeta(new BCDataStream($value));
    }
}