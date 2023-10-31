<?php

declare(strict_types=1);

namespace Slik\Image\Domain;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use Slik\Image\Domain\Event\ImageWasCreated;
use Slik\Image\Domain\ValueObject\ImageAttributes;
use Slik\Shared\Domain\ValueObject\ID;

class Image implements AggregateRoot {
  use AggregateRootBehaviour;
  
  private ImageAttributes $attributes;
  
  public function getAttributes(): ImageAttributes {
    return $this->attributes;
  }
  
  public function setAttributes(ImageAttributes $attributes): void {
    $this->attributes = $attributes;
  }
  
  /**
   */
  public static function create(ID $id, ImageAttributes $attributes): self {
    $image = new self($id);
    
    $image->recordThat(new ImageWasCreated($id, $attributes));
    
    return $image;
  }
  
  public function applyImageWasCreated(ImageWasCreated $event): void {
    $this->setAttributes($event->attributes);
  }
}