<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Symfony\Component\HttpFoundation\File\File;

final class ImageAnalyzer implements ImageAnalyzerInterface {
  private ?File $file = null;
  private ?int $height = null;
  private ?int $width = null;

  public function __construct(
    private readonly ImageCapabilityChecker $capabilityChecker
  ) {
  }

  /**
   * @param File $file
   * @return array<string, mixed>
   */
  public function analyze(File $file): array {
    $this->file = $file;
    [$this->width, $this->height] = getimagesize($file->getPathname()) ?: [null, null];

    return $this->toPayload();
  }

  public function isConversionRequired(?string $mimeType): bool {
    return $this->capabilityChecker->isConversionRequired($mimeType);
  }

  public function supportsExifProfile(?string $mimeType): bool {
    return $this->capabilityChecker->supportsExifProfile($mimeType);
  }

  public function supportsResize(?string $mimeType): bool {
    return $this->capabilityChecker->supportsResize($mimeType);
  }
  
  public function requiresSanitization(?string $mimeType): bool {
    return $this->capabilityChecker->requiresSanitization($mimeType);
  }

  public function supportsFormatConversion(?string $mimeType): bool {
    return $this->capabilityChecker->supportsFormatConversion($mimeType);
  }

  public function supportsAnimation(?string $mimeType): bool {
    return $this->capabilityChecker->supportsAnimation($mimeType);
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    if ($this->file === null) {
      return [];
    }

    return [
      'mimeType' => $this->file->getMimeType(),
      'size' => $this->file->getSize(),
      'timeCreated' => $this->file->getCTime(),
      'timeModified' => $this->file->getMTime(),
      'width' => $this->width ?? 0,
      'height' => $this->height ?? 0,
      'aspectRatio' => $this->getAspectRatio(),
      'orientation' => $this->getOrientation(),
    ];
  }

  private function getAspectRatio(): float {
    if ($this->width === null || $this->height === null || $this->width === 0 || $this->height === 0) {
      return 0;
    }

    return $this->width / $this->height;
  }

  private function getOrientation(): string {
    if ($this->width === null || $this->height === null) {
      return 'unknown';
    }

    if ($this->width === $this->height) {
      return 'square';
    }

    return $this->width > $this->height ? 'landscape' : 'portrait';
  }
}