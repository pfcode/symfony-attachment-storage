# Symfony Attachment Storage
This library provides abstraction layer for storage of attachments (images, videos and any files) that are indexed in 
database accessed by Doctrine ORM. Created with extensibility in mind, allows developer to quickly integrate their own
storage platforms, slug generation methods and attachment download methods.

### Installation
Add it to your project by:
```bash
composer require pfcode/symfony-attachment-storage
```

### Sample configuration
First of all, you need to create a Doctrine entity that implements `Pfcode\AttachmentStorage\Entity\AttachmentInterface` 
and implement its getters and setters. Here is an example:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pfcode\AttachmentStorage\Entity\AttachmentInterface;

/**
 * Class MyAttachment
 * @package Pfcode\AttachmentStorage
 * @ORM\Entity(repositoryClass="App\Repository\MyAttachmentRepository")
 */
class MyAttachment implements AttachmentInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", nullable=false)
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @var string|null
     */
    private $storageIdentifier;

    /**
     * @ORM\Column(type="string", length=8, nullable=false)
     * @var string|null
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string|null
     */
    private $mimeType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string|null
     */
    private $extension;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $fileSize = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string|null
     */
    private $originalName;

    public function setStorageIdentifier(?string $storageIdentifier): void {
        $this->storageIdentifier = $storageIdentifier;
    }

    public function getStorageIdentifier(): ?string {
        return $this->storageIdentifier;
    }

    public function setSlug(?string $slug): void {
        $this->slug = $slug;
    }

    public function getSlug(): ?string {
        return $this->slug;
    }

    public function setMimeType(?string $mimeType): void {
        $this->mimeType = $mimeType;
    }

    public function getMimeType(): ?string {
        return $this->mimeType;
    }

    public function setExtension(?string $extension): void {
        $this->extension = $extension;
    }

    public function getExtension(): ?string {
        return $this->extension;
    }

    public function setFileSize(int $bytes): void {
        $this->fileSize = $bytes;
    }

    public function getFileSize(): int {
        return $this->fileSize;
    }

    public function setOriginalName(?string $originalName): void {
        $this->originalName = $originalName;
    }

    public function getOriginalName(): ?string {
        return $this->originalName;
    }
    
    public function setId(?int $id): void {
        $this->id = $id;    
    }   

    public function getId(): ?int {
        return $this->id;
    }
}
```

Remember to create a repository for this entity! Here's a dummy implementation for `MyAttachment` sample:
```php
<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class MyAttachmentRepository extends EntityRepository {

}
```

Then, you should register a few services in your `config/services.yml` file:
```yaml
# Implementation of a simple storage service that uses directory on a local machine that is accessible publicly by URL
Pfcode\AttachmentStorage\Storage\LocalStorage:
    public: true
    bind:
      # Absolute path to directory used to store all files by this service 
      $absolutePath: '/var/www/public/images'
      # Relative path from directory accessible publicly by URL address in browser
      $baseUrl: '/images'

# Registry for all storage services that should be available in your project
Pfcode\AttachmentStorage\StorageRegistry\StorageRegistry:
    public: true
    calls: 
      # Register all storage services that you need. At least one is required
      - [registerStorage, ['@Pfcode\AttachmentStorage\Storage\LocalStorage']]
      # You should set default storage that will be used as a default method for uploading
      - [setDefaultStorage, ['@Pfcode\AttachmentStorage\Storage\LocalStorage']]

# Sample class used by AttachmentUploader to store files accessible on remote servers by URL
Pfcode\AttachmentStorage\Utils\Downloader\CurlDownloader:
    public: true
    
# Utility service used to recognize if file is an image, video or other type
Pfcode\AttachmentStorage\Utils\AttachmentDescriber:
    public: true

# Utility service used to suggest a file extension, when a file being uploaded doesn't have one
Pfcode\AttachmentStorage\Utils\ExtensionSuggester:
    public: true
    
# Sample service used to generate a slug for new uploaded attachments 
Pfcode\AttachmentStorage\Utils\SlugGenerator\SampleSlugGenerator:
    public: true
    arguments: ['@App\Repository\MyAttachmentRepository']

# Service used to upload new attachments
Pfcode\AttachmentStorage\Uploader\AttachmentUploader:
    public: true
    arguments: [
      '@Pfcode\AttachmentStorage\StorageRegistry\StorageRegistry', 
      '@Pfcode\AttachmentStorage\Utils\ExtensionSuggester',
      # Service implementing Pfcode\AttachmentStorage\Utils\SlugGenerator\SlugGeneratorInterface
      '@Pfcode\AttachmentStorage\Utils\SlugGenerator\SampleSlugGenerator',
      # Service implementing Pfcode\AttachmentStorage\Utils\Downloader\DownloaderInterface
      '@Pfcode\AttachmentStorage\Utils\Downloader\CurlDownloader'
    ]
    bind:
      # Specify class of entity that implements AttachmentInterface.
      # Warning! This is not a service! Just a string with Fully Qualified Class Name
      $entityClass: 'App\Entity\MyAttachment'
```

### Extending components
All components of this library are created with extensibility in mind, so you can implement available interfaces or
create new classes of existing services, register them in your `services.yml` file and reference them in other
services' configuration.

### License
This library is released under MIT license. 