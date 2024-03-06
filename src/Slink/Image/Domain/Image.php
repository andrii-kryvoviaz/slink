<?php

declare(strict_types=1);

namespace Slink\Image\Domain;

use Slink\Image\Domain\Event\ImageAttributesWasUpdated;
use Slink\Image\Domain\Event\ImageWasCreated;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\ID;

final class Image extends AbstractAggregateRoot {
  private ID $userId;
  
  private ImageAttributes $attributes;
  
  private ImageMetadata $metadata;
  
  /**
   * @return ID
   */
  public function getUserId(): ID {
    return $this->userId;
  }
  
  /**
   * @param ID $userId
   * @return void
   */
  public function setUserId(ID $userId): void {
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
   * @param ID $userId
   * @return bool
   */
  public function isOwedBy(ID $userId): bool {
    return $this->userId->equals($userId);
  }
  
  /**
   */
  public static function create(ID $id, ID $userId, ImageAttributes $attributes, ImageMetadata $metadata = null): self {
    $image = new self($id);
    
    $image->recordThat(new ImageWasCreated($id, $userId, $attributes, $metadata));
    
    return $image;
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
   * @param ImageAttributesWasUpdated $event
   * @return void
   */
  public function applyImageAttributesWasUpdated(ImageAttributesWasUpdated $event): void {
    $this->setAttributes($event->attributes);
  }
}