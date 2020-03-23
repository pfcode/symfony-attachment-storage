<?php


namespace Pfcode\AttachmentStorage\Storage;


use Pfcode\AttachmentStorage\Entity\AttachmentInterface;

interface StorageInterface
{
    /**
     * @param string $path
     * @param AttachmentInterface $attachment
     * @throws StorageException
     */
    public function uploadAttachmentFromPath(string $path, AttachmentInterface $attachment): void;

    /**
     * @param string $blob
     * @param AttachmentInterface $attachment
     * @throws StorageException
     */
    public function uploadAttachmentFromBlob(string $blob, AttachmentInterface $attachment): void;

    /**
     * @param AttachmentInterface $attachment
     * @param string $path
     * @throws StorageException
     */
    public function downloadAttachmentToLocal(AttachmentInterface $attachment, string $path): void;

    /**
     * @param AttachmentInterface $attachment
     * @return string|null
     * @throws StorageException
     */
    public function getUrlOfAttachment(AttachmentInterface $attachment): ?string;

    /**
     * @return string
     */
    public function getStorageIdentifier(): string;

    /**
     * @return bool
     */
    public function isStorageAvailable(): bool;

    /**
     * @param AttachmentInterface $attachment
     * @return bool
     */
    public function hasAttachment(AttachmentInterface $attachment): bool;

    /**
     * @param AttachmentInterface $attachment
     */
    public function removeAttachment(AttachmentInterface $attachment): void;
}