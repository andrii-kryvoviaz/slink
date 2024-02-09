<?php

declare(strict_types=1);

namespace Slink\Image\Domain;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use Slink\Image\Domain\Event\ImageAttributesWasUpdated;
use Slink\Image\Domain\Event\ImageWasCreated;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Domain\ValueObject\ID;

final class Image implements AggregateRoot {
  use AggregateRootBehaviour;
  
  private ImageAttributes $attributes;
  
  private ImageMetadata $metadata;
  
  public function getAttributes(): ImageAttributes {
    return $this->attributes;
  }
  
  public function setAttributes(ImageAttributes $attributes): void {
    $this->attributes = $attributes;
  }
  
  public function setMetadata(ImageMetadata $metadata): void {
    $this->metadata = $metadata;
  }
  
  public function getMetadata(): ImageMetadata {
    return $this->metadata;
  }
  
  public function hasExtension(string $extension): bool {
    return $this->getAttributes()->getExtension() === $extension;
  }
  
  /**
   */
  public static function create(ID $id, ImageAttributes $attributes, ImageMetadata $metadata = null): self {
    $image = new self($id);
    
    $image->recordThat(new ImageWasCreated($id, $attributes, $metadata));
    
    return $image;
  }
  
  public function addView(): void {
    $attributes = $this->getAttributes()->addView();
    
    $this->recordThat(new ImageAttributesWasUpdated($this->aggregateRootId(), $attributes));
  }
  
  public function updateAttributes(ImageAttributes $attributes): void {
    $this->recordThat(new ImageAttributesWasUpdated($this->aggregateRootId(), $attributes));
  }
  
  public function applyImageWasCreated(ImageWasCreated $event): void {
    $this->setAttributes($event->attributes);
    
    if ($event->metadata) {
      $this->setMetadata($event->metadata);
    }
  }
  
  public function applyImageAttributesWasUpdated(ImageAttributesWasUpdated $event): void {
    $this->setAttributes($event->attributes);
  }
}