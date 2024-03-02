<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;
use Slink\Shared\Infrastructure\Attribute\Groups;
use Slink\Shared\Infrastructure\Attribute\SerializedName;

#[ORM\Embeddable]
final readonly class ImageMetadata extends AbstractCompoundValueObject {
  
  public function __construct(
    #[ORM\Column(type: 'integer')]
    #[Groups(['public'])]
    private int    $size,
    
    #[ORM\Column(type: 'string')]
    #[Groups(['public'])]
    private string $mimeType,
    
    #[ORM\Column(type: 'integer')]
    #[Groups(['public'])]
    private int    $width,
    
    #[ORM\Column(type: 'integer')]
    #[Groups(['public'])]
    private int    $height,
  ) {
  }
  
  public function getSize(): int {
    return $this->size;
  }
  
  public function getMimeType(): string {
    return $this->mimeType;
  }
  
  public function getWidth(): int {
    return $this->width;
  }
  
  public function getHeight(): int {
    return $this->height;
  }
  
  public function toPayload(): array {
    return [
      'size' => $this->size,
      'mimeType' => $this->mimeType,
      'width' => $this->width,
      'height' => $this->height,
    ];
  }
  
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['size'],
      $payload['mimeType'],
      $payload['width'],
      $payload['height'],
    );
  }
}