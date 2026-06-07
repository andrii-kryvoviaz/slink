<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Image\Domain\ValueObject\Operation\ImageOperation;

interface ImageTransformationStrategyInterface
{
    public function supports(ImageTransformationRequest $request): bool;

    /**
     * @return ImageOperation[]
     */
    public function operations(ImageTransformationRequest $request): array;
}
