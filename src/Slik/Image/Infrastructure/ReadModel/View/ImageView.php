<?php

declare(strict_types=1);

namespace Slik\Image\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slik\Image\Domain\Event\ImageWasCreated;
use Slik\Image\Domain\ValueObject\ImageAttributes;
use Slik\Image\Infrastructure\ReadModel\Repository\ImageRepository;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\DateTime;
use Slik\Shared\Infrastructure\Persistence\ReadModel\AbstractView;

#[ORM\Table(name: '`image`')]
#[ORM\Entity(repositoryClass: ImageRepository::class)]
final readonly class ImageView extends AbstractView {
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $uuid,

    #[ORM\Embedded(class: ImageAttributes::class, columnPrefix: false)]
    private ImageAttributes $attributes,
  ) {
  }
  
  /**
   * @throws DateTimeException
   */
  public static function deserialize(array $payload): static {
    return new self(
      $payload['id'],
      ImageAttributes::fromPayload($payload['attributes']),
    );
  }
  
  public function getUuid(): string {
    return $this->uuid;
  }
  
  public function getAttributes(): ImageAttributes {
    return $this->attributes;
  }
}