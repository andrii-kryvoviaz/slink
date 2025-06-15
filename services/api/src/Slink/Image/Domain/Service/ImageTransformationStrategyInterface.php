<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;

interface ImageTransformationStrategyInterface
{
    public function supports(ImageTransformationRequest $request): bool;
    
    public function transform(
        string $imageContent,
        ImageDimensions $originalDimensions,
        ImageTransformationRequest $request
    ): string;
}
