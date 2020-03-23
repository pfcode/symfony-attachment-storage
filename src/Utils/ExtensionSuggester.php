<?php


namespace Pfcode\AttachmentStorage\Utils;


use Mimey\MimeTypes;

class ExtensionSuggester
{
    /**
     * @param string $mimeType
     * @param string|null $default
     * @return null|string
     */
    public function suggestExtension(string $mimeType, ?string $default = null): ?string {
        $mimes = new MimeTypes();
        return $mimes->getExtension($mimeType) ?? $default;
    }
}