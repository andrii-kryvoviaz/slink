<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\Persistence\ReadModel;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

abstract class AbstractView {
  public static function fromEvent(SerializablePayload $event): static {
    return static::deserialize($event->toPayload());
  }

  abstract public static function deserialize(array $payload): static;
  
  public function merge(AbstractView $view): static {
    $reflection = new \ReflectionClass($this);
    $properties = $reflection->getProperties();
    
    foreach ($properties as $property) {
      $value = $property->getValue($view);
      
      if ($value && !$property->isReadOnly()) {
        $property->setValue($this, $value);
      }
    }
    
    return $this;
  }
}