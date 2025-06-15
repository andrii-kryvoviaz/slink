<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Enum;

/**
 * Enum representing different strategies for resolving partial image dimensions.
 */
enum DimensionResolutionStrategy: string
{
    /**
     * Calculate missing dimension using aspect ratio preservation.
     * Used for resize operations to maintain image proportions.
     */
    case ASPECT_RATIO = 'aspect_ratio';

    /**
     * Use original image dimensions for missing sides.
     * Used for crop operations to ensure a real crop box.
     */
    case ORIGINAL = 'original';
}
