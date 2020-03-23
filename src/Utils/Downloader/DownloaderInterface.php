<?php

namespace Pfcode\AttachmentStorage\Utils\Downloader;

interface DownloaderInterface
{
    /**
     * @param string $url
     * @param bool $unsafeSSL
     * @return string
     * @throws DownloaderException
     */
    public function downloadFromUrlToTemporaryFile(string $url, bool $unsafeSSL = false): string;
}