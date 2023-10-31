<?php

declare(strict_types=1);

namespace Slik\Image\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slik\Image\Domain\Event\ImageWasCreated;
use Slik\Image\Infrastructure\ReadModel\Repository\ImageRepository;
use Slik\Shared\Domain\ValueObject\DateTime;

#[ORM\Table(name: '`image`')]
#[ORM\Entity(repositoryClass: ImageRepository::class)]
final readonly class ImageView {
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $uuid,

    #[ORM\Column(type: 'string')]
    private string $fileName,

    #[ORM\Column(type: 'string')]
    private string $description,

    #[ORM\Column(type: 'boolean')]
    private bool $isPublic,

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTime $createdAt,

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTime $updatedAt,
    
    #[ORM\Column(type: 'integer')]
    private ?int $views = 0,
  ) {
  }
  
  public static function fromEvent(ImageWasCreated $event): self {
    return new self(
      $event->id->toString(),
      $event->attributes->getFileName(),
      $event->attributes->getDescription(),
      $event->attributes->isPublic(),
      $event->attributes->getCreatedAt(),
      $event->attributes->getUpdatedAt(),
      $event->attributes->getViews(),
    );
  }
  
  public function getUuid(): string {
    return $this->uuid;
  }
  
  public function getFileName(): string {
    return $this->fileName;
  }
  
  public function getDescription(): string {
    return $this->description;
  }
  
  public function isPublic(): bool {
    return $this->isPublic;
  }
  
  public function getViews(): int {
    return $this->views;
  }
  
  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }
  
  public function getUpdatedAt(): ?DateTime {
    return $this->updatedAt;
  }
}