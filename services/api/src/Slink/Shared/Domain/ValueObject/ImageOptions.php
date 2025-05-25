<?php

namespace Slink\Shared\Domain\ValueObject;

final readonly class ImageOptions extends AbstractCompoundValueObject {
  
  private const array SKIP_PROPERTIES = ['fileName', 'mimeType'];
  private const array PROPERTY_MAP = [
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
   * @param bool|null $crop
   */
  private function __construct(
    private string $fileName,
    private string $mimeType,
    private ?int $width = null,
    private ?int $height = null,
    private ?int $quality = null,
    private ?bool $crop = null,
  ) {
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'fileName' => $this->fileName,
      'mimeType' => $this->mimeType,
      'width' => $this->width,
      'height' => $this->height,
      'quality' => $this->quality,
      'crop' => $this->crop,
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  public static function fromPayload(array $payload): static {
    return new static(
      fileName: $payload['fileName'],
      mimeType: $payload['mimeType'],
      width: $payload['width'] ?? null,
      height: $payload['height'] ?? null,
      quality: $payload['quality'] ?? null,
      crop: $payload['crop'] ?? null,
    );
  }
  
  /**
   * @param bool $isCache
   * @return string
   */
  public function getFileName(bool $isCache = false): string {
    if ($isCache) {
      return $this->getCacheFileName();
    }
    
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
      
      $name = self::PROPERTY_MAP[$property->getName()] ?? $property->getName();
      
      if(is_bool($value)) {
        if($value) {
          $carry[] = $name;
        }

        return $carry;
      }
      
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
  public function isCropped(): bool {
    return (bool) $this->crop;
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
    return !$this->isEmpty();
  }
  
  /**
   * @return string
   */
  public function __toString(): string {
    return $this->fileName;
  }
}