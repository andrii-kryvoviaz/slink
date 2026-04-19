<?php

declare(strict_types=1);

namespace Slink\Share\Domain;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use Slink\Share\Domain\AccessRule\ExpirationAware;
use Slink\Share\Domain\AccessRule\PasswordProtected;
use Slink\Share\Domain\AccessRule\PublicationAware;
use Slink\Share\Domain\Event\SharePasswordWasSet;
use Slink\Share\Domain\Event\ShareExpirationWasSet;
use Slink\Share\Domain\Event\ShareWasCreated;
use Slink\Share\Domain\Event\ShareWasPublished;
use Slink\Share\Domain\Event\ShortUrlWasAdded;
use Slink\Share\Domain\ValueObject\AccessControl;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class Share extends AbstractAggregateRoot implements PublicationAware, ExpirationAware, PasswordProtected {
  private ShareableReference $shareable;
  private TargetPath $targetPath;
  private DateTime $createdAt;
  private ShareContext $context;
  private AccessControl $accessControl;

  protected function __construct(ID $id) {
    parent::__construct($id);
    $this->accessControl = AccessControl::initial(false);
  }

  public static function create(
    ID $id,
    ShareableReference $shareable,
    TargetPath $targetPath,
    DateTime $createdAt,
    ShareContext $context,
  ): self {
    $share = new self($id);
    $share->recordThat(new ShareWasCreated(
      $id,
      $shareable,
      $targetPath,
      $createdAt,
      $context,
      $shareable->getShareableType()->autoPublishOnCreate(),
    ));

    return $share;
  }

  public function addShortUrl(ID $shortUrlId, string $shortCode): void {
    if ($this->context->hasShortUrl()) {
      return;
    }

    $this->recordThat(new ShortUrlWasAdded(
      $this->aggregateRootId(),
      $shortUrlId,
      $shortCode,
    ));
  }

  protected function applyShareWasCreated(ShareWasCreated $event): void {
    $this->shareable = $event->shareable;
    $this->targetPath = $event->targetPath;
    $this->createdAt = $event->createdAt;
    $this->context = $event->context;
    $this->accessControl = AccessControl::initial($event->isPublished);
  }

  protected function applyShortUrlWasAdded(ShortUrlWasAdded $event): void {
    $this->context = $this->context->withShortUrl($event->shortUrlId, $event->shortCode);
  }

  public function publish(): void {
    if ($this->accessControl->isPublished) {
      return;
    }

    $this->recordThat(new ShareWasPublished($this->aggregateRootId()));
  }

  protected function applyShareWasPublished(ShareWasPublished $event): void {
    $this->accessControl = $this->accessControl->publish();
  }

  public function setExpiration(?DateTime $expiresAt): void {
    $next = $this->accessControl->expireAt($expiresAt);

    if ($next === $this->accessControl) {
      return;
    }

    $this->recordThat(new ShareExpirationWasSet($this->aggregateRootId(), $expiresAt));
  }

  protected function applyShareExpirationWasSet(ShareExpirationWasSet $event): void {
    $this->accessControl = $this->accessControl->expireAt($event->expiresAt);
  }

  public function setPassword(?HashedSharePassword $password): void {
    $next = $this->accessControl->withPassword($password);

    if ($next === $this->accessControl) {
      return;
    }

    $this->recordThat(new SharePasswordWasSet($this->aggregateRootId(), $password?->toString()));
  }

  protected function applySharePasswordWasSet(SharePasswordWasSet $event): void {
    $hash = $event->passwordHash;

    if ($hash === null) {
      $this->accessControl = $this->accessControl->withPassword(null);
      return;
    }

    $this->accessControl = $this->accessControl->withPassword(HashedSharePassword::fromHash($hash));
  }

  public function isPublished(): bool {
    return $this->accessControl->isPublished;
  }

  public function getAccessControl(): AccessControl {
    return $this->accessControl;
  }

  public function getExpiresAt(): ?DateTime {
    return $this->accessControl->expiresAt;
  }

  public function getPassword(): ?HashedSharePassword {
    return $this->accessControl->getPassword();
  }

  public function getId(): string {
    return $this->aggregateRootId()->toString();
  }

  public function getShareable(): ShareableReference {
    return $this->shareable;
  }

  public function getShareableId(): string {
    return $this->shareable->getShareableId();
  }

  public function getTargetPath(): TargetPath {
    return $this->targetPath;
  }

  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }

  public function getContext(): ShareContext {
    return $this->context;
  }

  public function getShortCode(): ?string {
    return $this->context->getShortCode();
  }

  /**
   * @return array<string, mixed>
   */
  protected function createSnapshotState(): array {
    return [
      'shareable' => $this->shareable->toPayload(),
      'targetUrl' => $this->targetPath->toString(),
      'createdAt' => $this->createdAt->toString(),
      'context' => $this->context->toPayload(),
      'accessControl' => $this->accessControl->toPayload(),
    ];
  }

  /**
   * @param mixed $state
   */
  protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting {
    $share = new self(ID::fromString($id->toString()));

    $share->shareable = ShareableReference::fromPayload($state['shareable']);
    $share->targetPath = TargetPath::fromString($state['targetUrl']);
    $share->createdAt = DateTime::fromString($state['createdAt']);
    $share->context = ShareContext::fromPayload($state['context'] ?? [], $share->shareable);
    $share->accessControl = AccessControl::fromPayload($state['accessControl']);

    return $share;
  }
}
