<?php

namespace Slink\Image\Application\Service;

use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\Service\ImageProcessingOptions;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;

final readonly class GrpcImageTransformer implements ImageTransformerInterface
{
    public function __construct(
        private SettingsService $settingsService,
        private ImageProcessorInterface $imageProcessor
    ) {
    }

    public function resize(string $content, ?int $width, ?int $height): string
    {
        $options = new ImageProcessingOptions(
            width: $width,
            height: $height,
            crop: false,
        );

        $result = $this->imageProcessor->process($content, $options);
        return $result->getImageData();
    }

    public function crop(string $content, ?int $width, ?int $height): string
    {
        if (!$width && !$height) {
            return $content;
        }

        $options = new ImageProcessingOptions(
            width: $width ?? $height,
            height: $height ?? $width,
            crop: true,
        );

        $result = $this->imageProcessor->process($content, $options);
        return $result->getImageData();
    }

    public function transform(string $content, ImageOptions $imageOptions): string
    {
        $options = new ImageProcessingOptions(
            width: $imageOptions->getWidth(),
            height: $imageOptions->getHeight(),
            crop: $imageOptions->isCropped(),
        );

        $result = $this->imageProcessor->process($content, $options);
        return $result->getImageData();
    }

    public function stripExifMetadata(string $path): string
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return $path;
        }

        $options = new ImageProcessingOptions(
            stripMetadata: true,
            preserveAnimation: true
        );

        $result = $this->imageProcessor->process($content, $options);
        
        if ($result->isSuccessful() && $result->wasProcessed) {
            file_put_contents($path, $result->getImageData());
        }

        return $path;
    }

    public function convertToJpeg(SplFileInfo $file, ?int $quality = null): SplFileInfo
    {
        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            return $file;
        }

        $quality ??= $this->settingsService->get('image.compressionQuality');

        $options = new ImageProcessingOptions(
            quality: $quality,
            outputFormat: 'jpeg',
            preserveAnimation: false 
        );

        $result = $this->imageProcessor->process($content, $options);

        if ($result->isSuccessful() && $result->wasProcessed) {
            $fileName = $file->getBasename('.' . $file->getExtension());
            $jpegPath = sprintf('%s/%s.jpg', $file->getPath(), $fileName);
            
            file_put_contents($jpegPath, $result->getImageData());
            return new File($jpegPath, true);
        }

        return $file;
    }
}
