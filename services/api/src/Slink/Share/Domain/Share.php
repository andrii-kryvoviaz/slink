<?php

declare(strict_types=1);

namespace Slink\Share\Domain;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use Slink\Share\Domain\Event\ShareWasCreated;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class Share extends AbstractAggregateRoot {
  private ID $imageId;
  private string $targetUrl;
  private DateTime $createdAt;
  private ShareContext $context;

  protected function __construct(ID $id) {
    parent::__construct($id);
    $this->context = ShareContext::empty();
  }

  public static function create(
    ID $id,
    ID $imageId,
    string $targetUrl,
    DateTime $createdAt,
    ShareContext $context,
  ): self {
    $share = new self($id);
    $share->recordThat(new ShareWasCreated(
      $id,
      $imageId,
      $targetUrl,
      $createdAt,
      $context,
    ));

    return $share;
  }

  protected function applyShareWasCreated(ShareWasCreated $event): void {
    $this->imageId = $event->imageId;
    $this->targetUrl = $event->targetUrl;
    $this->createdAt = $event->createdAt;
    $this->context = $event->context;
  }

  public function getImageId(): ID {
    return $this->imageId;
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

  /**
   * @return array<string, mixed>
   */
  protected function createSnapshotState(): array {
    return [
      'imageId' => $this->imageId->toString(),
      'targetUrl' => $this->targetUrl,
      'createdAt' => $this->createdAt->toString(),
      'context' => $this->context->toPayload(),
    ];
  }

  /**
   * @param array<string, mixed> $state
   */
  protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting {
    $share = new self(ID::fromString($id->toString()));
    $share->imageId = ID::fromString($state['imageId']);
    $share->targetUrl = $state['targetUrl'];
    $share->createdAt = DateTime::fromString($state['createdAt']);
    $share->context = ShareContext::fromPayload($state['context'] ?? []);

    return $share;
  }
}
