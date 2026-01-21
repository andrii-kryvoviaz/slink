<?php

declare(strict_types=1);

namespace Slink\Share\Domain;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use Slink\Share\Domain\Event\ShareWasCreated;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class Share extends AbstractAggregateRoot {
  private ShareableReference $shareable;
  private string $targetUrl;
  private DateTime $createdAt;
  private ShareContext $context;

  protected function __construct(ID $id) {
    parent::__construct($id);
  }

  public static function create(
    ID $id,
    ShareableReference $shareable,
    string $targetUrl,
    DateTime $createdAt,
    ShareContext $context,
  ): self {
    $share = new self($id);
    $share->recordThat(new ShareWasCreated(
      $id,
      $shareable,
      $targetUrl,
      $createdAt,
      $context,
    ));

    return $share;
  }

  protected function applyShareWasCreated(ShareWasCreated $event): void {
    $this->shareable = $event->shareable;
    $this->targetUrl = $event->targetUrl;
    $this->createdAt = $event->createdAt;
    $this->context = $event->context;
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

  public function getContext(): ShareContext {
    return $this->context;
  }

  public function getShortCode(): ?string {
    return $this->context->getShortCode();
  }

  protected function createSnapshotState(): array {
    return [
      'shareable' => $this->shareable->toPayload(),
      'targetUrl' => $this->targetUrl,
      'createdAt' => $this->createdAt->toString(),
      'context' => $this->context->toPayload(),
    ];
  }

  protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting {
    $share = new self(ID::fromString($id->toString()));

    if (isset($state['imageId'])) {
      $share->shareable = ShareableReference::forImage(ID::fromString($state['imageId']));
    } else {
      $share->shareable = ShareableReference::fromPayload($state['shareable']);
    }

    $share->targetUrl = $state['targetUrl'];
    $share->createdAt = DateTime::fromString($state['createdAt']);
    $share->context = ShareContext::fromPayload($state['context'] ?? [], $share->shareable);

    return $share;
  }
}
