<?php

declare(strict_types=1);

namespace Slik\Image\Domain;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use Slik\Image\Domain\Event\ImageMetadataWasAdded;
use Slik\Image\Domain\Event\ImageWasCreated;
use Slik\Image\Domain\ValueObject\ImageAttributes;
use Slik\Image\Domain\ValueObject\ImageMetadata;
use Slik\Shared\Domain\ValueObject\ID;

class Image implements AggregateRoot {
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
  
  /**
   */
  public static function create(ID $id, ImageAttributes $attributes, ImageMetadata $metadata = null): self {
    $image = new self($id);
    
    $image->recordThat(new ImageWasCreated($id, $attributes, $metadata));
    
    return $image;
  }
  
  public function applyImageWasCreated(ImageWasCreated $event): void {
    $this->setAttributes($event->attributes);
    
    if ($event->metadata) {
      $this->setMetadata($event->metadata);
    }
  }
}