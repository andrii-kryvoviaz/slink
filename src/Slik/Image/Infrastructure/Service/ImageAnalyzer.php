<?php

declare(strict_types=1);

namespace Slik\Image\Infrastructure\Service;

use Slik\Image\Domain\Service\ImageAnalyzerInterface;
use Symfony\Component\HttpFoundation\File\File;

final class ImageAnalyzer implements ImageAnalyzerInterface {
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
   * @return self
   */
  public static function fromFile(File $file): self {
    $instance = new self();
    return $instance->setFile($file);
  }
  
  /**
   * @param string $path
   * @return self
   */
  public static function fromPath(string $path): self {
    $file = new File($path);
    
    return self::fromFile($file);
  }
  
  /**
   * @param File $file
   * @return void
   */
  public function analyze(File $file): void {
    $this->setFile($file);
    
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