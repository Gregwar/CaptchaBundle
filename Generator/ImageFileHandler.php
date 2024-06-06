<?php

declare(strict_types=1);

namespace Gregwar\CaptchaBundle\Generator;

use GdImage;
use Symfony\Component\Finder\Finder;

/**
 * Handles actions related to captcha image files including saving and garbage collection.
 *
 * @author Gregwar <g.passault@gmail.com>
 * @author Jeremy Livingston <jeremy@quizzle.com>
 */
class ImageFileHandler
{
    /**
     * Name of folder for captcha images.
     */
    protected string $imageFolder;

    /**
     * Absolute path to public web folder.
     */
    protected string $webPath;

    /**
     * Frequency of garbage collection in fractions of 1.
     */
    protected int $gcFreq;

    /**
     * Maximum age of images in minutes.
     */
    protected int $expiration;

    /**
     * @param string $imageFolder
     * @param string $webPath
     * @param int    $gcFreq
     * @param int    $expiration
     */
    public function __construct(string $imageFolder, string $webPath, int $gcFreq, int $expiration)
    {
        $this->imageFolder = $imageFolder;
        $this->webPath = $webPath;
        $this->gcFreq = $gcFreq;
        $this->expiration = $expiration;
    }

    public function saveAsFile(GdImage $contents): string
    {
        $this->createFolderIfMissing();

        $filename = md5(uniqid()).'.jpg';
        $filePath = $this->webPath.'/'.$this->imageFolder.'/'.$filename;
        imagejpeg($contents, $filePath, 15);

        return '/'.$this->imageFolder.'/'.$filename;
    }

    public function collectGarbage(): bool
    {
        if (1 != mt_rand(1, $this->gcFreq)) {
            return false;
        }

        $this->createFolderIfMissing();

        $finder = new Finder();
        $criteria = sprintf('<= now - %s minutes', $this->expiration);
        $finder->in($this->webPath.'/'.$this->imageFolder)
            ->date($criteria);

        foreach ($finder->files() as $file) {
            unlink($file->getPathname());
        }

        return true;
    }

    protected function createFolderIfMissing(): void
    {
        if (!file_exists($this->webPath.'/'.$this->imageFolder)) {
            mkdir($this->webPath.'/'.$this->imageFolder, 0755);
        }
    }
}
