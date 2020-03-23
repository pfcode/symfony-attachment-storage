<?php


namespace Pfcode\AttachmentStorage\Utils\Downloader;


class CurlDownloader implements DownloaderInterface
{
    /**
     * @inheritDoc
     */
    public function downloadFromUrlToTemporaryFile(string $url, bool $unsafeSSL = false, int $maxDownloadSize = 0, int $timeout = 5): string {
        $tmpFileName = tempnam(sys_get_temp_dir(), '_curl_');
        if ($tmpFileName === null) {
            @unlink($tmpFileName);
            throw new DownloaderException('Failed to create temporary file!');
        }

        $tmpFile = fopen($tmpFileName, 'wb+');
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

        fclose($tmpFile);

        register_shutdown_function(function() use($tmpFileName) {
            @unlink($tmpFileName);
        });

        if (!file_exists($tmpFileName)) {
            throw new DownloaderException('Failed to download a file!');
        }

        return $tmpFileName;
    }
}