<?php


namespace Pfcode\AttachmentStorage\Utils\Downloader;


class CurlDownloader implements DownloaderInterface
{
    /**
     * @inheritDoc
     */
    public function downloadFromUrlToTemporaryFile(string $url, bool $unsafeSSL = false, int $maxDownloadSize = 0, int $timeout = 5): string {
        $tmpFile = tmpfile();
        if ($tmpFile === false) {
            return null;
        }

        $tmpFileName = stream_get_meta_data($tmpFile);
        $tmpFileName = $tmpFileName['uri'] ?? null;
        if ($tmpFileName === null) {
            @unlink($tmpFile);
            throw new DownloaderException('Failed to create temporary file!');
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_FILE, $tmpFile);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_BUFFERSIZE, 128);
        curl_setopt($curl, CURLOPT_NOPROGRESS, false);

        if ($maxDownloadSize > 0) {
            curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, static function (
                /** @noinspection PhpUnusedParameterInspection */
                $DownloadSize, $Downloaded) use ($maxDownloadSize) {

                return ($Downloaded > $maxDownloadSize) ? 1 : 0;
            });
        }

        if ($unsafeSSL) {
            /** @noinspection CurlSslServerSpoofingInspection */
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            /** @noinspection CurlSslServerSpoofingInspection */
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        }

        curl_exec($curl);
        curl_close($curl);

        if (!file_exists($tmpFileName)) {
            throw new DownloaderException('Failed to download a file!');
        }

        return $tmpFileName;
    }
}