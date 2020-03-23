<?php

namespace Pfcode\AttachmentStorage\Utils\SlugGenerator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Pfcode\AttachmentStorage\Entity\AttachmentInterface;

class SampleSlugGenerator implements SlugGeneratorInterface
{
    public const DEFAULT_LENGTH = 8;
    public const DEFAULT_SLUG_COLUMN = 'slug';
    public const MAX_DUPLICATION_CHECKS = 10;

    /** @var ObjectRepository */
    private $repository;

    /** @var int */
    private $slugLength;

    /** @var string */
    private $slugColumn;

    /**
     * SampleSlugGenerator constructor.
     * @param EntityManagerInterface $em
     * @param string $entityClass
     * @param string $slugColumn
     * @param string $slugLength
     */
    public function __construct(EntityManagerInterface $em, string $entityClass, string $slugColumn = self::DEFAULT_SLUG_COLUMN, string $slugLength = self::DEFAULT_LENGTH) {
        $this->repository = $em->getRepository($entityClass);
        $this->slugLength = $slugLength;
        $this->slugColumn = $slugColumn;
    }

    /**
     * @param AttachmentInterface|null $attachment
     * @return string|null
     * @throws SlugGeneratorException
     */
    public function generate(?AttachmentInterface $attachment = null): ?string {
        $tries = self::MAX_DUPLICATION_CHECKS;
        $slug = null;
        if ($slug === null) {
            do {
                $slug = $this->randomAlphanumericString($this->slugLength);
                $tries--;
            } while ($this->repository->findOneBy([$this->slugColumn => $slug]) !== null && $tries > 0);

            if ($tries === 0) {
                throw new SlugGeneratorException('Exceeded maximum allowed slug duplication checks! Try using longer slugs.');
            }
        }

        return $slug;
    }

    /**
     * @param $length
     * @return string
     * @throws SlugGeneratorException
     */
    private function randomAlphanumericString($length): string {
        $allowedCharacters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $repeats = max(ceil($length / strlen($allowedCharacters)), 1);
        $str = substr(str_shuffle(str_repeat($allowedCharacters, $repeats)), 0, $length);

        if ($str === false) {
            throw new SlugGeneratorException('Failed to generate random string!');
        }

        return $str;
    }
}