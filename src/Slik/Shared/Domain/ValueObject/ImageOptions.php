<?php

namespace Slik\Shared\Domain\ValueObject;

final readonly class ImageOptions extends AbstractCompoundValueObject {
  
  /**
   * @param string $fileName
   * @param int|null $width
   * @param int|null $height
   * @param int|null $quality
   */
  private function __construct(
    private string $fileName,
    private ?int $width = null,
    private ?int $height = null,
    private ?int $quality = null,
  ) {
  }
  
  /**
   * @return array
   */
  public function toPayload(): array {
    return [
      'width' => $this->width,
      'height' => $this->height,
      'quality' => $this->quality,
    ];
  }
  
  /**
   * @param array $payload
   * @return static
   */
  public static function fromPayload(array $payload): static {
    return new static(
      fileName: $payload['fileName'],
      width: $payload['width'] ?? null,
      height: $payload['height'] ?? null,
      quality: $payload['quality'] ?? null,
    );
  }
  
  /**
   * @param string $fileName
   * @return static
   */
  public static function fromFileName(string $fileName): static {
    return new static(
      fileName: $fileName,
    );
  }
  
  /**
   * @return string
   */
  public function getFileName(): string {
    return $this->fileName;
  }
  
  /**
   * @return int|null
   */
  public function getWidth(): ?int {
    return $this->width;
  }
  
  /**
   * @return int|null
   */
  public function getHeight(): ?int {
    return $this->height;
  }
  
  /**
   * @return int|null
   */
  public function getQuality(): ?int {
    return $this->quality;
  }
  
  /**
   * @return bool
   */
  public function isEmpty(): bool {
    $reflection = new \ReflectionClass($this);
    
    foreach ($reflection->getProperties() as $property) {
      // Skip fileName
      if ($property->getName() === 'fileName') {
        continue;
      }
      
      if ($property->getValue($this) !== null) {
        return false;
      }
    }
    
    return true;
  }
  
  /**
   * @return bool
   */
  public function isModified(): bool {
    return !$this->isEmpty();
  }
  
  /**
   * @param ImageOptions $other
   * @return bool
   */
  public function compare(self $other): bool {
    return $this->width === $other->width
      && $this->height === $other->height
      && $this->quality === $other->quality;
  }
}