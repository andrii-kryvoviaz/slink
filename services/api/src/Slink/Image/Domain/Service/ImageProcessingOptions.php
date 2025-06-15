<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

final readonly class ImageProcessingOptions
{
    public function __construct(
        public ?int $width = null,
        public ?int $height = null,
        public bool $crop = false,
        public bool $preserveAnimation = true,
        public ?int $quality = null,
        public bool $stripMetadata = false,
        public string $outputFormat = 'auto'
    ) {
    }

    public function hasResizing(): bool
    {
        return $this->width !== null || $this->height !== null;
    }

    public function isAnimationPreserved(): bool
    {
        return $this->preserveAnimation;
    }
}
