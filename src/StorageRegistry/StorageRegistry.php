<?php


namespace Pfcode\AttachmentStorage\Registry;

use Pfcode\AttachmentStorage\Storage\StorageInterface;

class StorageRegistry
{
    /** @var StorageInterface[] */
    private $registeredStorageServicesByClass = [];

    /** @var StorageInterface[] */
    private $registeredStorageServicesByIdentifiers = [];

    /** @var StorageInterface|null */
    private $defaultStorage;

    /**
     * @param StorageInterface $storage
     * @throws StorageRegistryException
     */
    public function registerStorage(StorageInterface $storage): void {
        if (isset($this->registeredStorageServicesByClass[get_class($storage)])) {
            throw new StorageRegistryException('Tried to register storage that already is registered!');
        }

        $this->registeredStorageServicesByClass[get_class($storage)] = $storage;

        if (isset($this->registeredStorageServicesByIdentifiers[$storage->getStorageIdentifier()])) {
            throw new StorageRegistryException('Tried to register storage that has the same identifier as other, already registered storage!');
        }

        $this->registeredStorageServicesByIdentifiers[$storage->getStorageIdentifier()] = $storage;
    }

    /**
     * @param $identifier
     * @return StorageInterface
     * @throws StorageRegistryException
     */
    public function getStorageByIdentifier($identifier): StorageInterface {
        if (isset($this->registeredStorageServicesByIdentifiers[$identifier])) {
            throw new StorageRegistryException("Storage {$identifier} has not been found!");
        }

        return $this->registeredStorageServicesByIdentifiers[$identifier];
    }

    /**
     * @param $class
     * @return StorageInterface
     * @throws StorageRegistryException
     */
    public function getStorageByClass($class): StorageInterface {
        if (isset($this->registeredStorageServicesByClass[$class])) {
            throw new StorageRegistryException("Storage {$class} has not been found!");
        }

        return $this->registeredStorageServicesByClass[$class];
    }

    /**
     * @return array
     */
    public function getAllStorageServices(): array {
        $arr = [];
        foreach ($this->registeredStorageServicesByClass as $storage) {
            $arr[] = $storage;
        }

        return $arr;
    }

    /**
     * @param StorageInterface $storage
     * @return bool
     */
    public function hasStorage(StorageInterface $storage): bool {
        return isset($this->registeredStorageServicesByClass[get_class($storage)]);
    }

    /**
     * @param StorageInterface $storage
     * @throws StorageRegistryException
     */
    public function setDefaultStorage(StorageInterface $storage): void {
        if (!$this->hasStorage($storage)) {
            $this->registerStorage($storage);
        }

        $this->defaultStorage = $storage;
    }

    /**
     * @return StorageInterface
     */
    public function getDefaultStorage(): StorageInterface {
        return $this->defaultStorage;
    }
}