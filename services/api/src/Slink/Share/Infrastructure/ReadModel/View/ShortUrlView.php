<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\ReadModel\View;

use Doctrine\ORM\Mapping as ORM;
use Slink\Share\Infrastructure\ReadModel\Repository\ShortUrlRepository;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;

#[ORM\Table(name: '`short_url`')]
#[ORM\Entity(repositoryClass: ShortUrlRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_short_code', columns: ['short_code'])]
class ShortUrlView extends AbstractView {
  #[ORM\Id]
  #[ORM\Column(type: 'uuid')]
  private string $uuid;

  #[ORM\OneToOne(inversedBy: 'shortUrl', targetEntity: ShareView::class)]
  #[ORM\JoinColumn(name: 'share_id', referencedColumnName: 'uuid', nullable: false, onDelete: 'CASCADE')]
  private ShareView $share;

  #[ORM\Column(type: 'string', length: 8)]
  private string $shortCode;

  public function __construct(
    string $uuid,
    ShareView $share,
    string $shortCode,
  ) {
    $this->uuid = $uuid;
    $this->share = $share;
    $this->shortCode = $shortCode;
  }

  public function getId(): string {
    return $this->uuid;
  }

  public function getShare(): ShareView {
    return $this->share;
  }

  public function getShortCode(): string {
    return $this->shortCode;
  }

  public function getTargetUrl(): string {
    return $this->share->getTargetUrl();
  }
}
