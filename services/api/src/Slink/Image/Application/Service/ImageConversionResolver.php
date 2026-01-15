<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageConversionResolverInterface;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\HttpFoundation\File\File;

#[AsAlias(ImageConversionResolverInterface::class)]
final readonly class ImageConversionResolver implements ImageConversionResolverInterface {
  private const ImageFormat DEFAULT_FORMAT = ImageFormat::JPEG;

  public function __construct(
    private ImageAnalyzerInterface $imageAnalyzer,
    /** @var ConfigurationProviderInterface<SettingsService> */
    private ConfigurationProviderInterface $configurationProvider
  ) {
  }

  public function resolve(File $file): ?ImageFormat {
    $mimeType = $file->getMimeType();

    if ($this->imageAnalyzer->isConversionRequired($mimeType)) {
      return $this->getTargetFormat();
    }

    if ($this->shouldForceConversion($file)) {
      return $this->getTargetFormat();
    }

    return null;
  }

  private function shouldForceConversion(File $file): bool {
    if (!$this->configurationProvider->get('image.forceFormatConversion')) {
      return false;
    }

    $mimeType = $file->getMimeType();

    if (!$this->imageAnalyzer->supportsFormatConversion($mimeType)) {
      return false;
    }

    if ($this->isAnimatedImage($file) && !$this->configurationProvider->get('image.convertAnimatedImages')) {
      return false;
    }

    return true;
  }

  private function isAnimatedImage(File $file): bool {
    $mimeType = $file->getMimeType();

    return $this->imageAnalyzer->supportsAnimation($mimeType)
      && $this->imageAnalyzer->isAnimated($file->getPathname());
  }

  private function getTargetFormat(): ImageFormat {
    if ($this->configurationProvider->get('image.forceFormatConversion')) {
      $format = $this->configurationProvider->get('image.targetFormat');
      return $format ? ImageFormat::fromString($format) : self::DEFAULT_FORMAT;
    }

    return self::DEFAULT_FORMAT;
  }
}
