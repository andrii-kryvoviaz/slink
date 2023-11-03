<?php

declare(strict_types=1);

namespace Slik\Image\Domain\Service;

use Symfony\Component\HttpFoundation\File\File;

final class ImageAnalyzer {
  
  /**
   * @var int|null
   */
  private ?int $width = null;
  
  /**
   * @var int|null
   */
  private ?int $height = null;
  
  /**
   * @param File $file
   */
  private function __construct(private readonly File $file) {
  }
  
  /**
   * @param File $file
   * @return self
   */
  public static function fromFile(File $file): self {
    return new self($file);
  }
  
  /**
   * @param string $path
   * @return self
   */
  public static function fromPath(string $path): self {
    return new self(new File($path));
  }
  
  /**
   * @return void
   */
  public function analyze(): void {
    $mimeType = $this->file->getMimeType();
    
    if ($mimeType === 'image/svg+xml') {
      $this->handleSvg();
    } else {
      $this->handleImage();
    }
  }
  
  /**
   * @return void
   */
  private function handleSvg(): void {
    $svg = \simplexml_load_file($this->file->getRealPath());
    
    $this->width = (int) $svg['width'];
    $this->height = (int) $svg['height'];
  }
  
  /**
   * @return void
   */
  private function handleImage(): void {
    [$this->width, $this->height] = \getimagesize($this->file->getRealPath());
  }
  
  /**
   * @return string
   */
  public function getMimeType(): string {
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
    return $this->width;
  }
  
  /**
   * @return int
   */
  public function getHeight(): int {
    return $this->height;
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
   * @return array
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