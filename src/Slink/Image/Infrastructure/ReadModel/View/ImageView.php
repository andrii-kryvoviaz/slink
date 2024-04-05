<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\Repository\ImageRepository;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: '`image`')]
#[ORM\Entity(repositoryClass: ImageRepository::class)]
class ImageView extends AbstractView {
  /**
   * @param string $uuid
   * @param ?UserView $user
   * @param ImageAttributes $attributes
   * @param ImageMetadata|null $metadata
   */
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[Groups(['public'])]
    #[SerializedName('id')]
    private readonly string $uuid,
    
    #[ORM\ManyToOne(targetEntity: UserView::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'uuid')]
    #[Groups(['public'])]
    #[SerializedName('owner')]
    private readonly ?UserView $user,

    #[ORM\Embedded(class: ImageAttributes::class, columnPrefix: false)]
    #[Groups(['public'])]
    private ImageAttributes $attributes,
    
    #[ORM\Embedded(class: ImageMetadata::class, columnPrefix: false)]
    #[Groups(['public'])]
    private ?ImageMetadata $metadata = null,
  ) {
  }
  
  /**
   * @return string
   */
  public function getUuid(): string {
    return $this->uuid;
  }
  
  /**
   * @return ?UserView
   */
  public function getUser(): ?UserView {
    return $this->user;
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
   * @return string
   */
  public function getFileName(): string {
    return $this->attributes->getFileName();
  }
  
  /**
   * @return string|null
   */
  public function getDescription(): ?string {
    return $this->attributes->getDescription();
  }
  
  /**
   * @return string
   */
  public function getMimeType(): string {
    return $this->metadata?->getMimeType() ?? 'unknown';
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->uuid,
      'user' => $this->user,
      ...$this->attributes->toPayload(),
      ...$this->metadata? $this->metadata->toPayload() : []
    ];
  }
}