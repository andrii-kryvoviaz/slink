<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Infrastructure\ReadModel\Repository\ShareRepository;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;

#[ORM\Table(name: '`share`')]
#[ORM\Entity(repositoryClass: ShareRepository::class)]
#[ORM\Index(columns: ['shareable_id', 'shareable_type'], name: 'idx_share_shareable')]
#[ORM\Index(columns: ['target_url'], name: 'idx_share_target_url')]
class ShareView extends AbstractView {
  #[ORM\Id]
  #[ORM\Column(type: 'uuid')]
  private string $uuid;

  #[ORM\Embedded(class: ShareableReference::class, columnPrefix: false)]
  private ShareableReference $shareable;

  #[ORM\Column(type: 'string', length: 2048)]
  private string $targetUrl;

  #[ORM\Column(type: 'datetime_immutable')]
  private DateTime $createdAt;

  #[ORM\OneToOne(mappedBy: 'share', targetEntity: ShortUrlView::class, cascade: ['persist', 'remove'])]
  private ?ShortUrlView $shortUrl = null;

  public function __construct(
    string $uuid,
    ShareableReference $shareable,
    string $targetUrl,
    DateTime $createdAt,
  ) {
    $this->uuid = $uuid;
    $this->shareable = $shareable;
    $this->targetUrl = $targetUrl;
    $this->createdAt = $createdAt;
  }

  public function getId(): string {
    return $this->uuid;
  }

  public function getShareable(): ShareableReference {
    return $this->shareable;
  }

  public function getShareableId(): string {
    return $this->shareable->getShareableId();
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
