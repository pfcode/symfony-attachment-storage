<?php


namespace Pfcode\AttachmentStorage\Utils;


use Pfcode\AttachmentStorage\Entity\AttachmentInterface;

class AttachmentDescriber
{
    /**
     * @param AttachmentInterface $attachment
     * @return bool
     */
    public function isImage(AttachmentInterface $attachment): bool {
        return strpos((string)$attachment->getMimeType(), 'image/') === 0;
    }

    /**
     * @param AttachmentInterface $attachment
     * @return bool
     */
    public function isVideo(AttachmentInterface $attachment): bool {
        return strpos((string)$attachment->getMimeType(), 'video/') === 0;
    }

    /**
     * @param AttachmentInterface $attachment
     * @return bool
     */
    public function isPdf(AttachmentInterface $attachment): bool {
        return in_array($attachment->getMimeType(), [
            'application/pdf',
            'application/x-pdf',
        ], true);
    }
}