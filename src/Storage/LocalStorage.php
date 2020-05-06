<?php


namespace Pfcode\AttachmentStorage\Storage;


use Pfcode\AttachmentStorage\Entity\AttachmentInterface;

class LocalStorage implements StorageInterface
{
    /**
     * @var string
     */
    protected $absolutePath;
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * LocalStorage constructor.
     * @param string $absolutePath
     * @param string $baseUrl
     */
    public function __construct(string $absolutePath, string $baseUrl) {
        $this->absolutePath = rtrim($absolutePath, '/');
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * @param string $path
     * @param AttachmentInterface $attachment
     * @throws StorageException
     */
    public function uploadAttachmentFromPath(string $path, AttachmentInterface $attachment): void {
        if (!$this->isStorageAvailable()) {
            throw new StorageException('This storage is not currently available.');
        }

        $fileName = $this->getAttachmentFileName($attachment);
        if (!$fileName) {
            throw new StorageException('Cannot upload an Attachment without having slug set first.');
        }

        $destinationPath = "{$this->absolutePath}/{$fileName}";
        if (copy($path, $destinationPath) === false) {
            throw new StorageException('Failed to copy a file.');
        }

        $this->updateAttachmentInfo($destinationPath, $attachment);
    }

    /**
     * @param string $blob
     * @param AttachmentInterface $attachment
     * @throws StorageException
     */
    public function uploadAttachmentFromBlob(string $blob, AttachmentInterface $attachment): void {
        if (!$this->isStorageAvailable()) {
            throw new StorageException('This storage is not currently available.');
        }

        $fileName = $this->getAttachmentFileName($attachment);
        if (!$fileName) {
            throw new StorageException('Cannot upload an Attachment without having slug set first.');
        }

        $destinationPath = "{$this->absolutePath}/{$fileName}";
        if (file_put_contents($destinationPath, $blob) === false) {
            throw new StorageException('Failed to save a file.');
        }

        $this->updateAttachmentInfo($destinationPath, $attachment);
    }

    /**
     * @param AttachmentInterface $attachment
     * @param string $path
     * @throws StorageException
     */
    public function downloadAttachmentToLocal(AttachmentInterface $attachment, string $path): void {
        if (!$this->isStorageAvailable()) {
            throw new StorageException('This storage is not currently available.');
        }

        if (!$this->hasAttachment($attachment)) {
            throw new StorageException('This storage does not store this attachment.');
        }

        $fileName = $this->getAttachmentFileName($attachment);
        if (!$fileName) {
            throw new StorageException('Cannot upload an Attachment without having slug set first.');
        }

        $sourcePath = "{$this->absolutePath}/{$fileName}";
        if (is_dir($path)) {
            $destinationPath = "{$path}/{$fileName}";
        } else {
            $destinationPath = $path;
        }

        if (copy($sourcePath, $destinationPath) === false) {
            throw new StorageException('Failed to save a file.');
        }
    }

    /**
     * @param AttachmentInterface $attachment
     * @return string|null
     */
    public function getUrlOfAttachment(AttachmentInterface $attachment): ?string {
        $fileName = $this->getAttachmentFileName($attachment);
        if ($fileName === null) {
            return null;
        }

        return "{$this->baseUrl}/{$fileName}";
    }

    /**
     * @return string
     */
    public function getStorageIdentifier(): string {
        return 'local';
    }

    /**
     * @return bool
     */
    public function isStorageAvailable(): bool {
        return file_exists($this->absolutePath)
            && is_dir($this->absolutePath)
            && is_readable($this->absolutePath)
            && is_writable($this->absolutePath);
    }

    /**
     * @param AttachmentInterface $attachment
     * @return bool
     */
    public function hasAttachment(AttachmentInterface $attachment): bool {
        $fileName = $this->getAttachmentFileName($attachment);
        return file_exists("{$this->absolutePath}/{$fileName}");
    }

    /**
     * @param AttachmentInterface $attachment
     */
    public function removeAttachment(AttachmentInterface $attachment): void {
        $fileName = $this->getAttachmentFileName($attachment);
        if ($fileName !== null && $this->hasAttachment($attachment)) {
            unlink("{$this->absolutePath}/{$fileName}");
        }
    }

    /**
     * @param AttachmentInterface $attachment
     * @return string
     */
    protected function getAttachmentFileName(AttachmentInterface $attachment): ?string {
        $ext = trim($attachment->getExtension());
        $slug = trim($attachment->getSlug());

        if (empty($slug)) {
            return null;
        }

        if (empty($ext)) {
            return $slug;
        }

        return "{$slug}.{$ext}";
    }

    /**
     * @param string $path
     * @param AttachmentInterface $attachment
     */
    protected function updateAttachmentInfo(string $path, AttachmentInterface $attachment): void {
        $attachment->setFileSize((int)filesize($path));
        $attachment->setMimeType(mime_content_type($path));
        $attachment->setStorageIdentifier($this->getStorageIdentifier());
    }
}