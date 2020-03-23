<?php


namespace Pfcode\AttachmentStorage\Utils\SlugGenerator;


use Pfcode\AttachmentStorage\Entity\AttachmentInterface;

class NullSlugGenerator implements SlugGeneratorInterface
{
    /**
     * @param AttachmentInterface|null $attachment
     * @return string|null
     */
    public function generate(?AttachmentInterface $attachment = null): ?string {
        return null;
    }
}