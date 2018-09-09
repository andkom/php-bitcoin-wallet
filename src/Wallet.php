<?php

declare(strict_types=1);

namespace AndKom\PhpBitcoinWallet;

use AndKom\BCDataStream\Reader;
use AndKom\PhpBerkeleyDb\Adapter\AdapterFactory;
use AndKom\PhpBerkeleyDb\Adapter\AdapterInterface;
use AndKom\PhpBerkeleyDb\Exception as DbException;
use AndKom\PhpBitcoinWallet\Item\EncryptedKey;
use AndKom\PhpBitcoinWallet\Item\ItemFactory;
use AndKom\PhpBitcoinWallet\Item\Key;
use AndKom\PhpBitcoinWallet\Item\MasterKey;

/**
 * Class Wallet
 * @package AndKom\PhpBitcoinWallet
 */
class Wallet
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var ItemFactory
     */
    protected $factory;

    /**
     * @var Key[]
     */
    protected $keys = [];

    /**
     * @var MasterKey
     */
    protected $masterKey;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Wallet constructor.
     * @param AdapterInterface|null $adapter
     * @param ItemFactory|null $factory
     */
    public function __construct(AdapterInterface $adapter = null, ItemFactory $factory = null)
    {
        if (is_null($adapter)) {
            $adapter = AdapterFactory::create();
        }

        if (is_null($factory)) {
            $factory = new ItemFactory($adapter);
        }

        $this->adapter = $adapter;
        $this->factory = $factory;
    }

    /**
     * @param string $filename
     * @return Wallet
     * @throws Exception
     */
    public function read(string $filename): self
    {
        try {
            $this->adapter->open($filename, 'r', 'main');

            $this->parse();

            $this->adapter->close();
        } catch (DbException $exception) {
            throw new Exception('Unable to parse wallet file.');
        }

        return $this;
    }

    /**
     * @return Wallet
     * @throws DbException
     */
    protected function parse(): self
    {
        $this->keys = [];
        $this->masterKey;

        foreach ($this->adapter->read() as $key => $value) {
            $kds = new Reader($key);
            $vds = new Reader($value);

            $type = $kds->readString();

            switch ($type) {
                case 'key':
                    $this->keys[] = $this->factory->createKey($kds, $vds);
                    break;

                case 'wkey':
                    $this->keys[] = $this->factory->createWKey($kds, $vds);
                    break;

                case 'ckey':
                    $this->keys[] = $this->factory->createEncryptedKey($kds, $vds);
                    break;

                case 'mkey':
                    $this->masterKey = $this->factory->createMasterKey($kds, $vds);
                    break;

                case 'version':
                case 'minversion':
                    $this->attributes[$type] = $vds->readUInt32();
                    break;
            }
        }

        return $this;
    }

    /**
     * @return Key[]
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * @return bool
     */
    public function isEncrypted(): bool
    {
        return !!$this->masterKey;
    }

    /**
     * @return MasterKey
     * @throws Exception
     */
    public function getMasterKey(): MasterKey
    {
        if (!$this->masterKey) {
            throw new Exception('Master key was not found.');
        }

        return $this->masterKey;
    }

    /**
     * @param string $passphrase
     * @return Wallet
     * @throws Exception
     */
    public function decrypt(string $passphrase): self
    {
        if (!$this->isEncrypted()) {
            throw new Exception('Wallet is not encrypted.');
        }

        $masterKey = $this->getMasterKey()->decrypt($passphrase);

        foreach ($this->keys as $key) {
            if ($key instanceof EncryptedKey) {
                $key->decrypt($masterKey);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}