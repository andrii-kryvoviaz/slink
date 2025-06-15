<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Factory;

use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;

final class ImageTransformationRequestFactory
{
    public static function createResizeRequest(
        int $width,
        int $height,
        bool $allowEnlarge = false
    ): ImageTransformationRequest {
        return new ImageTransformationRequest(
            targetDimensions: new ImageDimensions($width, $height),
            crop: false,
            allowEnlarge: $allowEnlarge
        );
    }

    public static function createCropRequest(
        int $width,
        int $height,
        bool $allowEnlarge = false
    ): ImageTransformationRequest {
        return new ImageTransformationRequest(
            targetDimensions: new ImageDimensions($width, $height),
            crop: true,
            allowEnlarge: $allowEnlarge
        );
    }

    public static function createThumbnailRequest(int $maxSize): ImageTransformationRequest
    {
        return new ImageTransformationRequest(
            targetDimensions: new ImageDimensions($maxSize, $maxSize),
            crop: false,
            allowEnlarge: false
        );
    }

    public static function createQualityOptimizationRequest(int $quality): ImageTransformationRequest
    {
        return new ImageTransformationRequest(
            quality: $quality
        );
    }

    /**
     * Creates a transformation request from ImageOptions with proper dimension handling.
     * 
     * @param \Slink\Shared\Domain\ValueObject\ImageOptions $options
     * @param ImageDimensions|null $originalDimensions Original image dimensions for aspect ratio calculation
     */
    public static function createFromImageOptions(
        \Slink\Shared\Domain\ValueObject\ImageOptions $options,
        ?ImageDimensions $originalDimensions = null
    ): ImageTransformationRequest {
        $targetDimensions = null;
        
        if ($options->getWidth() || $options->getHeight()) {
            if ($originalDimensions !== null) {
                // Use the enhanced factory method with aspect ratio calculation
                $targetDimensions = ImageDimensions::createWithAspectRatio(
                    $options->getWidth(),
                    $options->getHeight(),
                    $originalDimensions
                );
            } elseif ($options->getWidth() && $options->getHeight()) {
                // Both dimensions provided, no aspect ratio calculation needed
                $targetDimensions = new ImageDimensions(
                    $options->getWidth(),
                    $options->getHeight()
                );
            } else {
                // Only one dimension provided but no original dimensions for aspect ratio
                throw new \InvalidArgumentException(
                    'Original image dimensions required for aspect ratio calculation when only one dimension is specified'
                );
            }
        }

        return new ImageTransformationRequest(
            targetDimensions: $targetDimensions,
            crop: $options->isCropped(),
            quality: $options->getQuality()
        );
    }

    /**
     * Creates a resize request with aspect ratio preservation.
     */
    public static function createResizeWithAspectRatio(
        ?int $width,
        ?int $height,
        ImageDimensions $originalDimensions,
        bool $allowEnlarge = false
    ): ImageTransformationRequest {
        $targetDimensions = ImageDimensions::createWithAspectRatio($width, $height, $originalDimensions);
        
        return new ImageTransformationRequest(
            targetDimensions: $targetDimensions,
            crop: false,
            allowEnlarge: $allowEnlarge
        );
    }

    /**
     * Creates a crop request with aspect ratio preservation.
     */
    public static function createCropWithAspectRatio(
        ?int $width,
        ?int $height,
        ImageDimensions $originalDimensions,
        bool $allowEnlarge = false
    ): ImageTransformationRequest {
        $targetDimensions = ImageDimensions::createWithAspectRatio($width, $height, $originalDimensions);
        
        return new ImageTransformationRequest(
            targetDimensions: $targetDimensions,
            crop: true,
            allowEnlarge: $allowEnlarge
        );
    }
}
