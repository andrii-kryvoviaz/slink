<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

final readonly class ImageProcessingResult
{
    public function __construct(
        public string $imageData,
        public bool $wasProcessed,
        public ?string $errorMessage = null
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->errorMessage === null;
    }

    public function getImageData(): string
    {
        return $this->imageData;
    }
}
