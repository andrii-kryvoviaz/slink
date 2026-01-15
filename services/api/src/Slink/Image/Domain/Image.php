<?php

declare(strict_types=1);

namespace Slink\Image\Domain;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use Slink\Image\Domain\Context\ImageCreationContext;
use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\Event\ImageAttributesWasUpdated;
use Slink\Image\Domain\Event\ImageLicenseWasUpdated;
use Slink\Image\Domain\Event\ImageMetadataWasUpdated;
use Slink\Image\Domain\Event\ImageWasCreated;
use Slink\Image\Domain\Event\ImageWasDeleted;
use Slink\Image\Domain\Event\ImageWasTagged;
use Slink\Image\Domain\Event\ImageWasUntagged;
use Slink\Image\Domain\Exception\DuplicateImageException;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageFile;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Domain\ValueObject\TagSet;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\ID;

final class Image extends AbstractAggregateRoot {
  private ?ID $userId;

  private ImageAttributes $attributes;

  private ImageMetadata $metadata;

  private TagSet $tags;

  private ?License $license = null;

  private bool $deleted = false;

  /**
   * @param ID $id
   */
  protected function __construct(ID $id) {
    parent::__construct($id);
    
    $this->tags = TagSet::create();
  }

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
   * @param ID $tagId
   * @return bool
   */
  public function hasTag(ID $tagId): bool {
    return $this->tags->contains($tagId);
  }

  /**
   * @return TagSet
   */
  public function getTags(): TagSet {
    return $this->tags;
  }

  /**
   * @return int
   */
  public function getTagCount(): int {
    return $this->tags->count();
  }

  /**
   * @return bool
   */
  public function hasAnyTags(): bool {
    return !$this->tags->isEmpty();
  }

  /**
   * @return array<string>
   */
  public function getTagIds(): array {
    return $this->tags->toArray();
  }

  /**
   * @throws DuplicateImageException
   */
  public static function create(
    ID                    $id,
    ?ID                   $userId,
    ImageAttributes       $attributes,
    ImageMetadata         $metadata,
    ?ImageCreationContext $context = null,
    ?ImageFile            $imageFile = null,
    ?License              $license = null
  ): self {
    if ($imageFile && $context) {
      $context->duplicateSpecification->ensureNoDuplicate($imageFile, $userId);
    }

    $image = new self($id);

    $image->recordThat(new ImageWasCreated($id, $userId, $attributes, $metadata, $license));

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

    $this->license = $event->license;
  }

  public function applyImageLicenseWasUpdated(ImageLicenseWasUpdated $event): void {
    $this->license = $event->license;
  }

  public function getLicense(): ?License {
    return $this->license;
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

  public function updateLicense(?License $license): void {
    $this->recordThat(new ImageLicenseWasUpdated($this->aggregateRootId(), $license));
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
   * @param ID $tagId
   * @param ID $userId
   * @return void
   */
  public function tagWith(ID $tagId, ID $userId): void {
    if ($this->hasTag($tagId)) {
      return;
    }

    $this->recordThat(new ImageWasTagged($this->aggregateRootId(), $tagId, $userId));
  }

  /**
   * @param ID $tagId
   * @param ID $userId
   * @return void
   */
  public function removeTag(ID $tagId, ID $userId): void {
    if (!$this->hasTag($tagId)) {
      return;
    }

    $this->recordThat(new ImageWasUntagged($this->aggregateRootId(), $tagId, $userId));
  }

  /**
   * @param ImageWasDeleted $event
   * @return void
   */
  public function applyImageWasDeleted(ImageWasDeleted $event): void {
    $this->deleted = true;
  }

  /**
   * @param ImageWasTagged $event
   * @return void
   */
  public function applyImageWasTagged(ImageWasTagged $event): void {
    $this->tags->addTag($event->tagId);
  }

  /**
   * @param ImageWasUntagged $event
   * @return void
   */
  public function applyImageWasUntagged(ImageWasUntagged $event): void {
    $this->tags->removeTag($event->tagId);
  }

  /**
   * @return array<string, mixed>
   */
  protected function createSnapshotState(): array {
    return [
      'userId' => $this->userId?->toString(),
      'attributes' => $this->attributes->toPayload(),
      'metadata' => $this->metadata->toPayload(),
      'tags' => $this->tags->toPayload(),
      'license' => $this->license?->value,
      'deleted' => $this->deleted,
    ];
  }

  /**
   * @param array<string, mixed> $state
   */
  protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting {
    $image = new static(ID::fromString($id->toString()));

    $image->userId = $state['userId'] ? ID::fromString($state['userId']) : null;
    $image->attributes = ImageAttributes::fromPayload($state['attributes']);
    $image->metadata = ImageMetadata::fromPayload($state['metadata']);
    $image->tags = TagSet::fromPayload($state['tags'] ?? ['tags' => []]);
    $image->license = isset($state['license']) ? License::tryFrom($state['license']) : null;
    $image->deleted = $state['deleted'];

    return $image;
  }
}