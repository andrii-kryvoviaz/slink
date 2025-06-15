<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

interface ImageProcessorInterface
{
    public function process(string $imageData, ImageProcessingOptions $options): ImageProcessingResult;
}
