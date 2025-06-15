<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Enum;

/**
 * Enum representing different animation handling strategies for image processing.
 */
enum AnimationStrategy: string
{
    /**
     * Process only the first frame of animated images.
     * Results in a static image output.
     */
    case FIRST_FRAME_ONLY = 'first_frame_only';

    /**
     * Preserve all frames in animated images.
     * Maintains animation in the output.
     */
    case PRESERVE_ANIMATION = 'preserve_animation';

    /**
     * Auto-detect based on image type and processor capabilities.
     * Falls back to first frame if animation is not supported.
     */
    case AUTO = 'auto';
}
