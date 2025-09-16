<?php

declare(strict_types=1);

namespace Slink\Image\Domain;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use Slink\Image\Domain\Context\ImageCreationContext;
use Slink\Image\Domain\Event\ImageAttributesWasUpdated;
use Slink\Image\Domain\Event\ImageMetadataWasUpdated;
use Slink\Image\Domain\Event\ImageWasCreated;
use Slink\Image\Domain\Event\ImageWasDeleted;
use Slink\Image\Domain\Exception\DuplicateImageException;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageFile;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\ID;

final class Image extends AbstractAggregateRoot {
  private ?ID $userId;

  private ImageAttributes $attributes;

  private ImageMetadata $metadata;

  private bool $deleted = false;

  /**
   * @return ID|null
   */
  public function getUserId(): ?ID {
    return $this->userId;
  }

  /**
   * @param ID|null $userId
   * @return void
   */
  public function setUserId(?ID $userId): void {
    $this->userId = $userId;
  }

  /**
   * @return ImageAttributes
   */
  public function getAttributes(): ImageAttributes {
    return $this->attributes;
  }

  /**
   * @param ImageAttributes $attributes
   * @return void
   */
  public function setAttributes(ImageAttributes $attributes): void {
    $this->attributes = $attributes;
  }

  /**
   * @param ImageMetadata $metadata
   * @return void
   */
  public function setMetadata(ImageMetadata $metadata): void {
    $this->metadata = $metadata;
  }

  /**
   * @return ImageMetadata
   */
  public function getMetadata(): ImageMetadata {
    return $this->metadata;
  }

  /**
   * @param string $extension
   * @return bool
   */
  public function hasExtension(string $extension): bool {
    return $this->getAttributes()->getExtension() === $extension;
  }

  /**
   * @return bool
   */
  public function isDeleted(): bool {
    return $this->deleted;
  }

  /**
   * @param ID $userId
   * @return bool
   */
  public function isOwedBy(ID $userId): bool {
    return $this->userId?->equals($userId) ?? false;
  }

  /**
   * @throws DuplicateImageException
   */
  public static function create(
    ID                    $id,
    ?ID                   $userId,
    ImageAttributes       $attributes,
    ImageMetadata         $metadata,
    ?ImageFile            $imageFile = null,
    ?ImageCreationContext $context = null
  ): self {
    if ($imageFile && $context) {
      $context->duplicateSpecification->ensureNoDuplicate($imageFile, $userId);
    }

    $image = new self($id);

    $image->recordThat(new ImageWasCreated($id, $userId, $attributes, $metadata));

    return $image;
  }

  /**
   * @param ImageWasCreated $event
   * @return void
   */
  public function applyImageWasCreated(ImageWasCreated $event): void {
    $this->setUserId($event->userId);
    $this->setAttributes($event->attributes);

    if ($event->metadata) {
      $this->setMetadata($event->metadata);
    }
  }

  /**
   * @return void
   */
  public function addView(): void {
    $attributes = $this->getAttributes()->addView();

    $this->recordThat(new ImageAttributesWasUpdated($this->aggregateRootId(), $attributes));
  }

  /**
   * @param ImageAttributes $attributes
   * @return void
   */
  public function updateAttributes(ImageAttributes $attributes): void {
    $this->recordThat(new ImageAttributesWasUpdated($this->aggregateRootId(), $attributes));
  }

  /**
   * @param ImageMetadata $metadata
   * @return void
   */
  public function updateMetadata(ImageMetadata $metadata): void {
    $this->recordThat(new ImageMetadataWasUpdated($this->aggregateRootId(), $metadata));
  }

  /**
   * @param ImageAttributesWasUpdated $event
   * @return void
   */
  public function applyImageAttributesWasUpdated(ImageAttributesWasUpdated $event): void {
    $this->setAttributes($event->attributes);
  }

  /**
   * @param ImageMetadataWasUpdated $event
   * @return void
   */
  public function applyImageMetadataWasUpdated(ImageMetadataWasUpdated $event): void {
    $this->setMetadata($event->metadata);
  }

  /**
   * @param bool $preserveOnDisk
   * @return void
   */
  public function delete(bool $preserveOnDisk = false): void {
    $this->recordThat(new ImageWasDeleted($this->aggregateRootId(), $preserveOnDisk));
  }

  /**
   * @param ImageWasDeleted $event
   * @return void
   */
  public function applyImageWasDeleted(ImageWasDeleted $event): void {
    $this->deleted = true;
  }

  /**
   * @return array<string, mixed>
   */
  protected function createSnapshotState(): array {
    return [
      'userId' => $this->userId?->toString(),
      'attributes' => $this->attributes->toPayload(),
      'metadata' => $this->metadata->toPayload(),
      'deleted' => $this->deleted,
    ];
  }

  /**
   * @param array<string, mixed> $state
   */
  protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting {
    $image = new static($id);

    $image->userId = $state['userId'] ? ID::fromString($state['userId']) : null;
    $image->attributes = ImageAttributes::fromPayload($state['attributes']);
    $image->metadata = ImageMetadata::fromPayload($state['metadata']);
    $image->deleted = $state['deleted'];

    return $image;
  }
}