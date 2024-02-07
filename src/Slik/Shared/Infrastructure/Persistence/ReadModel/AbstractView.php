<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\Persistence\ReadModel;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

abstract class AbstractView {
  /**
   * @param SerializablePayload $event
   * @return static
   */
  public static function fromEvent(SerializablePayload $event): static {
    return static::deserialize($event->toPayload());
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  abstract public static function deserialize(array $payload): static;
  
  /**
   * @param AbstractView $view
   * @return $this
   */
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