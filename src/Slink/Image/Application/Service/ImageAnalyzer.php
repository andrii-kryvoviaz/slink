<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Symfony\Component\HttpFoundation\File\File;

final class ImageAnalyzer implements ImageAnalyzerInterface {
  /**
   * @param array<string> $resizableMimeTypes
   * @param array<string> $stripExifMimeTypes
   */
  public function __construct(
    private readonly array $resizableMimeTypes,
    private readonly array $stripExifMimeTypes,
  ) {
  }
  
  private File $file;
  
  /**
   * @var int|null
   */
  private ?int $width = null;
  
  /**
   * @var int|null
   */
  private ?int $height = null;
  
  public function setFile(File $file): self {
    $this->file = $file;
    
    return $this;
  }
  
  /**
   * @param File $file
   * @return array<string, mixed>
   */
  public function analyze(File $file): array {
    $this->setFile($file);
    
    [$this->width, $this->height] = getimagesize($file->getPathname()) ?: [1, 1];
    
    return $this->toPayload();
  }
  
  /**
   * @param ?string $mimeType
   * @return bool
   */
  public function supportsResize(?string $mimeType): bool {
    return \in_array($mimeType, $this->resizableMimeTypes, true);
  }
  
  /**
   * @param ?string $mimeType
   * @return bool
   */
  public function supportsExifProfile(?string $mimeType): bool {
    return \in_array($mimeType, $this->stripExifMimeTypes, true);
  }
  
  /**
   * @return ?string
   */
  public function getMimeType(): ?string {
    return $this->file->getMimeType();
  }
  
  /**
   * @return int
   */
  public function getSize(): int {
    return $this->file->getSize();
  }
  
  /**
   * @return int
   */
  public function getTimeCreated(): int {
    return $this->file->getCTime();
  }
  
  /**
   * @return int
   */
  public function getTimeModified(): int {
    return $this->file->getMTime();
  }
  
  /**
   * @return int
   */
  public function getWidth(): int {
    return $this->width ?? 0;
  }
  
  /**
   * @return int
   */
  public function getHeight(): int {
    return $this->height ?? 0;
  }
  
  /**
   * @return float
   */
  public function getAspectRatio(): float {
    if(!$this->width || !$this->height) {
      return 0;
    }
    
    return $this->width / $this->height;
  }
  
  /**
   * @return string
   */
  public function getOrientation(): string {
    if ($this->width === null || $this->height === null) {
      return 'unknown';
    }
    
    if ($this->width === $this->height) {
      return 'square';
    }
    
    return $this->width > $this->height ? 'landscape' : 'portrait';
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'mimeType' => $this->getMimeType(),
      'size' => $this->getSize(),
      'timeCreated' => $this->getTimeCreated(),
      'timeModified' => $this->getTimeModified(),
      'width' => $this->getWidth(),
      'height' => $this->getHeight(),
      'aspectRatio' => $this->getAspectRatio(),
      'orientation' => $this->getOrientation(),
    ];
  }
}