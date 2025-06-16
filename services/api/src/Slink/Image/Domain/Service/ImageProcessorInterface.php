<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Slink\Image\Domain\ValueObject\AnimatedImageInfo;

interface ImageProcessorInterface
{
    public function resize(
        string $imageContent, 
        int $width, 
        int $height
    ): string;
    
    public function crop(
        string $imageContent,
        int $width,
        int $height,
        int $x = 0,
        int $y = 0
    ): string;
    
    public function stripMetadata(string $path): string;
    
    public function convertFormat(string $imageContent, string $format, ?int $quality = null): string;
    
    /**
     * @param string $imageContent
     * @return array{int, int}
     */
    public function getImageDimensions(string $imageContent): array;

    public function getAnimatedImageInfo(string $imageContent): AnimatedImageInfo;
}
