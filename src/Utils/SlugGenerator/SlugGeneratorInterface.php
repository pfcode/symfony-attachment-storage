<?php

namespace Pfcode\AttachmentStorage\Utils\SlugGenerator;


use Pfcode\AttachmentStorage\Entity\AttachmentInterface;

interface SlugGeneratorInterface
{
    /**
     * @param AttachmentInterface|null $attachment
     * @return string|null
     */
    public function generate(?AttachmentInterface $attachment = null): ?string;
}