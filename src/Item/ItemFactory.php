<?php

declare(strict_types=1);

namespace AndKom\PhpBitcoinWallet\Item;

use AndKom\BCDataStream\Reader;
use AndKom\BCDataStream\Writer;
use AndKom\PhpBerkeleyDb\Adapter\AdapterInterface;

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
     * @param Reader $kds
     * @param Reader $vds
     * @return Key
     * @throws \AndKom\PhpBerkeleyDb\Exception
     */
    public function createKey(Reader $kds, Reader $vds): Key
    {
        $public = $kds->readString();
        $private = $vds->readString();

        return new Key($public, $private, $this->getKeyMeta($public));
    }

    /**
     * @param Reader $kds
     * @param Reader $vds
     * @return EncryptedKey
     * @throws \AndKom\PhpBerkeleyDb\Exception
     */
    public function createEncryptedKey(Reader $kds, Reader $vds): EncryptedKey
    {
        $public = $kds->readString();
        $encrypted = $vds->readString();

        return new EncryptedKey($public, $encrypted, $this->getKeyMeta($public));
    }

    /**
     * @param Reader $kds
     * @param Reader $vds
     * @return WKey
     * @throws \AndKom\PhpBerkeleyDb\Exception
     */
    public function createWKey(Reader $kds, Reader $vds): WKey
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
     * @param Reader $kds
     * @param Reader $vds
     * @return MasterKey
     */
    public function createMasterKey(Reader $kds, Reader $vds): MasterKey
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
     * @param Reader $vds
     * @return KeyMeta
     */
    public function createKeyMeta(Reader $vds): KeyMeta
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
        $key = (new Writer())
            ->writeString('keymeta')
            ->writeString($public)
            ->getBuffer();

        $value = $this->adapter->get($key);

        if (!$value) {
            return null;
        }

        return $this->createKeyMeta(new Reader($value));
    }
}