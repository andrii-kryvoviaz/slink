<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\Repository\ImageRepository;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;

#[ORM\Table(name: '`image`')]
#[ORM\Entity(repositoryClass: ImageRepository::class)]
final class ImageView extends AbstractView {
  /**
   * @param string $uuid
   * @param string $userId
   * @param ImageAttributes $attributes
   * @param ImageMetadata|null $metadata
   */
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private readonly string $uuid,
    
    #[ORM\Column(type: 'uuid', nullable: true)]
    private readonly string $userId,

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
      $payload['userId'] ?? '',
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
   * @return string
   */
  public function getUserId(): string {
    return $this->userId;
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
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->uuid,
      'userId' => $this->userId,
      ...$this->attributes->toPayload(),
      ...$this->metadata? $this->metadata->toPayload() : []
    ];
  }
}