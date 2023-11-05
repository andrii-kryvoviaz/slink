<?php

namespace Slik\Shared\Domain\ValueObject;

final readonly class ImageOptions extends AbstractCompoundValueObject {
  
  private const SKIP_PROPERTIES = ['fileName', 'mimeType'];
  private const PROPERTY_MAP = [
    'width' => 'w',
    'height' => 'h',
    'quality' => 'q',
  ];
  
  /**
   * @param string $fileName
   * @param string $mimeType
   * @param int|null $width
   * @param int|null $height
   * @param int|null $quality
   */
  private function __construct(
    private string $fileName,
    private string $mimeType,
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
      'fileName' => $this->fileName,
      'mimeType' => $this->mimeType,
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
      mimeType: $payload['mimeType'],
      width: $payload['width'] ?? null,
      height: $payload['height'] ?? null,
      quality: $payload['quality'] ?? null,
    );
  }
  
  /**
   * @return string
   */
  public function getFileName(): string {
    return $this->fileName;
  }
  
  /**
   * @return string
   */
  public function getMimeType(): string {
    return $this->mimeType;
  }
  
  /**
   * @return string
   */
  public function getCacheFileName(): string {
    [$name, $extension] = explode('.', $this->fileName);
    
    $reflection = new \ReflectionClass($this);
    
    $options = array_reduce($reflection->getProperties(), function ($carry, $property) {
      $value = $property->getValue($this);
      
      if (in_array($property->getName(), self::SKIP_PROPERTIES)) {
        return $carry;
      }
      
      if ($value === null) {
        return $carry;
      }
      
      $name = self::PROPERTY_MAP[$property->getName()] ?? $property->getName()[0];
      
      $carry[] = sprintf('%s%s', $name, $value);
      
      return $carry;
    }, []);
    
    return sprintf('%s-%s.%s', $name, implode('-', $options), $extension);
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
      if (in_array($property->getName(), self::SKIP_PROPERTIES)) {
        continue;
      }
      
      if ($property->getValue($this)) {
        return false;
      }
    }
    
    return true;
  }
  
  /**
   * @return bool
   */
  public function isModified(): bool {
    if(in_array($this->mimeType, ['image/svg+xml', 'image/gif'])) {
      return false;
    }
    
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
  
  /**
   * @return string
   */
  public function __toString(): string {
    return $this->fileName;
  }
}