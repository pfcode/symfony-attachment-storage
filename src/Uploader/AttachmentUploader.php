<?php


namespace Pfcode\AttachmentStorage\Uploader;


use LogicException;
use Pfcode\AttachmentStorage\Entity\AttachmentInterface;
use Pfcode\AttachmentStorage\Storage\StorageException;
use Pfcode\AttachmentStorage\Storage\StorageInterface;
use Pfcode\AttachmentStorage\StorageRegistry\StorageRegistry;
use Pfcode\AttachmentStorage\StorageRegistry\StorageRegistryException;
use Pfcode\AttachmentStorage\Utils\Downloader\DownloaderException;
use Pfcode\AttachmentStorage\Utils\Downloader\DownloaderInterface;
use Pfcode\AttachmentStorage\Utils\ExtensionSuggester;
use Pfcode\AttachmentStorage\Utils\SlugGenerator\SlugGeneratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AttachmentUploader
{
    /** @var StorageRegistry */
    protected $storageRegistry;

    /** @var string */
    protected $entityClass;

    /** @var ExtensionSuggester */
    protected $extensionSuggester;

    /** @var SlugGeneratorInterface */
    protected $slugGenerator;

    /** @var DownloaderInterface */
    protected $downloader;

    /**
     * AttachmentRepository constructor.
     * @param StorageRegistry $storageRegistry
     * @param ExtensionSuggester $extensionSuggester
     * @param SlugGeneratorInterface $slugGenerator
     * @param DownloaderInterface $downloader
     * @param string $entityClass
     */
    public function __construct(StorageRegistry $storageRegistry, ExtensionSuggester $extensionSuggester,
                                SlugGeneratorInterface $slugGenerator, DownloaderInterface $downloader, string $entityClass) {
        $this->extensionSuggester = $extensionSuggester;
        $this->storageRegistry = $storageRegistry;
        $this->slugGenerator = $slugGenerator;
        $this->entityClass = $entityClass;
        $this->downloader = $downloader;

        $entityInterfaces = class_implements($entityClass);
        if (!isset($entityInterfaces[AttachmentInterface::class])) {
            throw new LogicException('AttachmentUploader needs valid entity class that implements AttachmentInterface!');
        }
    }

    /**
     * @param string $path
     * @param string|null $slug
     * @param string|null $extension
     * @param StorageInterface|null $storage
     * @return AttachmentInterface
     * @throws StorageException
     * @throws UploaderException
     */
    public function uploadFromPath(string $path, ?string $slug = null, ?string $extension = null, ?StorageInterface $storage = null): AttachmentInterface {
        if (!file_exists($path)) {
            throw new UploaderException('Source file does not exist!');
        }

        if (!is_readable($path)) {
            throw new UploaderException('Source file is not readable!');
        }

        $mimeType = mime_content_type($path);
        if ($extension === null) {
            $extension = (string)pathinfo($path, PATHINFO_EXTENSION);
            if (empty($extension)) {
                $extension = $this->extensionSuggester->suggestExtension($mimeType);
            }
        }

        /** @var AttachmentInterface $attachment */
        $className = $this->entityClass;
        $attachment = new $className;
        $attachment->setMimeType($mimeType);
        $attachment->setExtension($extension);
        $attachment->setFileSize(filesize($path));

        if ($slug === null) {
            $slug = $this->slugGenerator->generate($attachment);
        }

        $attachment->setSlug($slug);

        if ($storage === null) {
            $storage = $this->storageRegistry->getDefaultStorage();
        }

        $storage->uploadAttachmentFromPath($path, $attachment);
        $attachment->setStorageIdentifier($storage->getStorageIdentifier());

        return $attachment;
    }

    /**
     * @param string $url
     * @param string|null $slug
     * @param string|null $extension
     * @param StorageInterface|null $storage
     * @return AttachmentInterface
     * @throws StorageException
     * @throws UploaderException
     * @throws DownloaderException
     */
    public function uploadFromURL(string $url, ?string $slug = null, ?string $extension = null, ?StorageInterface $storage = null): AttachmentInterface {
        $path = $this->downloader->downloadFromUrlToTemporaryFile($url);
        return $this->uploadFromPath($path, $slug, $extension, $storage);
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param string|null $slug
     * @param string|null $extension
     * @param StorageInterface|null $storage
     * @return AttachmentInterface
     * @throws StorageException
     * @throws UploaderException
     */
    public function uploadFromUploadedFile(UploadedFile $uploadedFile, ?string $slug = null, ?string $extension = null, ?StorageInterface $storage = null): AttachmentInterface {
        $attachment = $this->uploadFromPath($uploadedFile->getRealPath(), $slug, $extension, $storage);
        $attachment->setOriginalName($uploadedFile->getClientOriginalName());
        return $attachment;
    }

    /**
     * @param string $blob
     * @param string|null $slug
     * @param string|null $extension
     * @param StorageInterface|null $storage
     * @return AttachmentInterface
     * @throws StorageException
     */
    public function uploadFromBlob(string $blob, ?string $slug = null, ?string $extension = null, ?StorageInterface $storage = null): AttachmentInterface {
        /** @var AttachmentInterface $attachment */
        $className = $this->entityClass;
        $attachment = new $className;
        $attachment->setExtension($extension);
        $attachment->setFileSize(strlen($blob));

        if ($slug === null) {
            $slug = $this->slugGenerator->generate($attachment);
        }

        $attachment->setSlug($slug);

        if ($storage === null) {
            $storage = $this->storageRegistry->getDefaultStorage();
        }

        $storage->uploadAttachmentFromBlob($blob, $attachment);
        $attachment->setStorageIdentifier($storage->getStorageIdentifier());

        return $attachment;
    }

    /**
     * @param AttachmentInterface $attachment
     * @return StorageInterface
     * @throws UploaderException
     * @throws StorageRegistryException
     */
    public function getStorageOfAttachment(AttachmentInterface $attachment): StorageInterface {
        $storage = $this->storageRegistry->getStorageByIdentifier($attachment->getStorageIdentifier());
        if (!$storage) {
            throw new UploaderException('Storage of given attachment has not been found!');
        }

        return $storage;
    }
}