<?php

declare(strict_types=1);

namespace Slink\Share\Domain;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use Slink\Share\Domain\Event\ShareWasCreated;
use Slink\Share\Domain\Event\ShortUrlWasCreated;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class Share extends AbstractAggregateRoot {
  private ID $imageId;
  private string $targetUrl;
  private DateTime $createdAt;
  private ?ID $shortUrlId = null;
  private ?string $shortCode = null;

  protected function __construct(ID $id) {
    parent::__construct($id);
  }

  public static function create(
    ID $id,
    ID $imageId,
    string $targetUrl,
    DateTime $createdAt,
  ): self {
    $share = new self($id);
    $share->recordThat(new ShareWasCreated(
      $id,
      $imageId,
      $targetUrl,
      $createdAt,
    ));

    return $share;
  }

  public function createShortUrl(ID $shortUrlId, string $shortCode): void {
    $this->recordThat(new ShortUrlWasCreated(
      $this->aggregateRootId(),
      $shortUrlId,
      $shortCode,
    ));
  }

  protected function applyShareWasCreated(ShareWasCreated $event): void {
    $this->imageId = $event->imageId;
    $this->targetUrl = $event->targetUrl;
    $this->createdAt = $event->createdAt;
  }

  protected function applyShortUrlWasCreated(ShortUrlWasCreated $event): void {
    $this->shortUrlId = $event->shortUrlId;
    $this->shortCode = $event->shortCode;
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

  public function getShortUrlId(): ?ID {
    return $this->shortUrlId;
  }

  public function getShortCode(): ?string {
    return $this->shortCode;
  }

  /**
   * @return array<string, mixed>
   */
  protected function createSnapshotState(): array {
    return [
      'imageId' => $this->imageId->toString(),
      'targetUrl' => $this->targetUrl,
      'createdAt' => $this->createdAt->toString(),
      'shortUrlId' => $this->shortUrlId?->toString(),
      'shortCode' => $this->shortCode,
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
    $share->shortUrlId = $state['shortUrlId'] ? ID::fromString($state['shortUrlId']) : null;
    $share->shortCode = $state['shortCode'];

    return $share;
  }
}
