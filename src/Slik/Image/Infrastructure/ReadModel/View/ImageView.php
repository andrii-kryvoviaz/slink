<?php

declare(strict_types=1);

namespace Slik\Image\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slik\Image\Domain\ValueObject\ImageAttributes;
use Slik\Image\Domain\ValueObject\ImageMetadata;
use Slik\Image\Infrastructure\ReadModel\Repository\ImageRepository;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Infrastructure\Persistence\ReadModel\AbstractView;

#[ORM\Table(name: '`image`')]
#[ORM\Entity(repositoryClass: ImageRepository::class)]
final class ImageView extends AbstractView {
  /**
   * @param string $uuid
   * @param ImageAttributes $attributes
   * @param ImageMetadata|null $metadata
   */
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private readonly string $uuid,

    #[ORM\Embedded(class: ImageAttributes::class, columnPrefix: false)]
    private ImageAttributes $attributes,
    
    #[ORM\Embedded(class: ImageMetadata::class, columnPrefix: false)]
    private ?ImageMetadata $metadata = null,
  ) {
  }
  
  /**
   * @throws DateTimeException
   */
  public static function deserialize(array $payload): static {
    return new self(
      $payload['id'],
      ImageAttributes::fromPayload($payload['attributes']),
      isset($payload['metadata'])? ImageMetadata::fromPayload($payload['metadata']) : null,
    );
  }
  
  /**
   * @return string
   */
  public function getUuid(): string {
    return $this->uuid;
  }
  
  /**
   * @return ImageAttributes
   */
  public function getAttributes(): ImageAttributes {
    return $this->attributes;
  }
  
  /**
   * @return ImageMetadata|null
   */
  public function getMetadata(): ?ImageMetadata {
    return $this->metadata;
  }
  
  /**
   * @return array
   */
  public function toPayload(): array {
    return [
      'id' => $this->uuid,
      ...$this->attributes->toPayload(),
      ...$this->metadata? $this->metadata->toPayload() : []
    ];
  }
}