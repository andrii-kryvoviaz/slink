<?php

declare(strict_types=1);

namespace Slik\Image\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\AbstractCompoundValueObject;
use Slik\Shared\Domain\ValueObject\DateTime;

#[ORM\Embeddable]
final readonly class ImageMetadata extends AbstractCompoundValueObject{
  
  public function __construct(
    #[ORM\Column(type: 'integer')]
    private int    $size,
    
    #[ORM\Column(type: 'string')]
    private string $mimeType,
    
    #[ORM\Column(type: 'integer')]
    private int    $width,
    
    #[ORM\Column(type: 'integer')]
    private int    $height,
  ) {
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