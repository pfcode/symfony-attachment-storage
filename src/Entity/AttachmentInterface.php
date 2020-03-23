<?php


namespace Pfcode\AttachmentStorage\Entity;


interface AttachmentInterface
{
    public function setStorageIdentifier(?string $storageIdentifier): void;

    public function getStorageIdentifier(): ?string;

    public function setSlug(?string $slug): void;

    public function getSlug(): ?string;

    public function setMimeType(?string $mimeType): void;

    public function getMimeType(): ?string;

    public function setExtension(?string $extension): void;

    public function getExtension(): ?string;

    public function setFileSize(int $bytes): void;

    public function getFileSize(): int;

    public function setOriginalName(?string $originalName): void;

    public function getOriginalName(): ?string;
}