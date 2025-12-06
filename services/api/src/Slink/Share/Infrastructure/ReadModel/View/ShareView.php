<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Share\Infrastructure\ReadModel\Repository\ShareRepository;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;

#[ORM\Table(name: '`share`')]
#[ORM\Entity(repositoryClass: ShareRepository::class)]
#[ORM\Index(columns: ['image_id'], name: 'idx_share_image')]
#[ORM\Index(columns: ['target_url'], name: 'idx_share_target_url')]
class ShareView extends AbstractView {
  #[ORM\Id]
  #[ORM\Column(type: 'uuid')]
  private string $uuid;

  #[ORM\ManyToOne(targetEntity: ImageView::class)]
  #[ORM\JoinColumn(name: 'image_id', referencedColumnName: 'uuid', nullable: false)]
  private ImageView $image;

  #[ORM\Column(type: 'string', length: 2048)]
  private string $targetUrl;

  #[ORM\Column(type: 'datetime_immutable')]
  private DateTime $createdAt;

  #[ORM\OneToOne(mappedBy: 'share', targetEntity: ShortUrlView::class, cascade: ['persist', 'remove'])]
  private ?ShortUrlView $shortUrl = null;

  public function __construct(
    string $uuid,
    ImageView $image,
    string $targetUrl,
    DateTime $createdAt,
  ) {
    $this->uuid = $uuid;
    $this->image = $image;
    $this->targetUrl = $targetUrl;
    $this->createdAt = $createdAt;
  }

  public function getId(): string {
    return $this->uuid;
  }

  public function getImage(): ImageView {
    return $this->image;
  }

  public function getImageId(): string {
    return $this->image->getUuid();
  }

  public function getTargetUrl(): string {
    return $this->targetUrl;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getShortUrl(): ?ShortUrlView {
    return $this->shortUrl;
  }

  public function setShortUrl(ShortUrlView $shortUrl): void {
    $this->shortUrl = $shortUrl;
  }
}
