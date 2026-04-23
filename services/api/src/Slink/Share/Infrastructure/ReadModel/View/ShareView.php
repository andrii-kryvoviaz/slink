<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\ReadModel\View;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Slink\Share\Domain\AccessRule\ExpirationAware;
use Slink\Share\Domain\AccessRule\PasswordProtected;
use Slink\Share\Domain\AccessRule\PublicationAware;
use Slink\Share\Domain\ValueObject\AccessControl;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Infrastructure\ReadModel\Repository\ShareRepository;
use Slink\Shared\Domain\Contract\CursorAwareInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;

#[ORM\Table(name: '`share`')]
#[ORM\Entity(repositoryClass: ShareRepository::class)]
#[ORM\Index(columns: ['shareable_id', 'shareable_type'], name: 'idx_share_shareable')]
#[ORM\Index(columns: ['target_url'], name: 'idx_share_target_url')]
#[ORM\Index(columns: ['is_published', 'created_at', 'uuid'], name: 'idx_share_listing')]
#[ORM\Index(columns: ['expires_at'], name: 'idx_share_expires_at')]
class ShareView extends AbstractView implements PublicationAware, ExpirationAware, PasswordProtected, CursorAwareInterface {
  #[ORM\Id]
  #[ORM\Column(type: 'uuid')]
  private string $uuid;

  #[ORM\Embedded(class: ShareableReference::class, columnPrefix: false)]
  private ShareableReference $shareable;

  #[ORM\Column(type: 'string', length: 2048)]
  private string $targetUrl;

  #[ORM\Column(type: 'datetime_immutable')]
  private DateTime $createdAt;

  #[ORM\Embedded(class: AccessControl::class, columnPrefix: false)]
  private AccessControl $accessControl;

  #[ORM\OneToOne(mappedBy: 'share', targetEntity: ShortUrlView::class, cascade: ['persist', 'remove'])]
  private ?ShortUrlView $shortUrl = null;

  public function __construct(
    string $uuid,
    ShareableReference $shareable,
    string $targetUrl,
    DateTime $createdAt,
    ?AccessControl $accessControl = null,
  ) {
    $this->uuid = $uuid;
    $this->shareable = $shareable;
    $this->targetUrl = $targetUrl;
    $this->createdAt = $createdAt;
    $this->accessControl = $accessControl ?? AccessControl::initial(false);
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

  public function getTargetPath(): string {
    return $this->targetUrl;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function markPublished(): void {
    $this->accessControl = $this->accessControl->publish();
  }

  public function markUnpublished(): void {
    $this->accessControl = $this->accessControl->unpublish();
  }

  public function expireAt(?DateTime $expiresAt): void {
    $this->accessControl = $this->accessControl->expireAt($expiresAt);
  }

  public function setPassword(?HashedSharePassword $password): void {
    $this->accessControl = $this->accessControl->withPassword($password);
  }

  public function isPublished(): bool {
    return $this->accessControl->isPublished;
  }

  public function getExpiresAt(): ?DateTime {
    return $this->accessControl->expiresAt;
  }

  public function getPassword(): ?HashedSharePassword {
    return $this->accessControl->getPassword();
  }

  public function getShortUrl(): ?ShortUrlView {
    return $this->shortUrl;
  }

  public function getShortCode(): ?string {
    return $this->shortUrl?->getShortCode();
  }

  public function setShortUrl(ShortUrlView $shortUrl): void {
    $this->shortUrl = $shortUrl;
  }

  public function getCursorId(): string {
    return $this->uuid;
  }

  public function getCursorTimestamp(): DateTimeInterface {
    return $this->createdAt;
  }
}
