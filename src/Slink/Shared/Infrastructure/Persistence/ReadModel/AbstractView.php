<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\ReadModel;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

abstract class AbstractView {
  /**
   * @param SerializablePayload $event
   * @param callable|null $transformer
   * @return static
   */
  public static function fromEvent(SerializablePayload $event, Callable $transformer = null): static {
    $payload = $event->toPayload();
    
    if ($transformer) {
      $payload = $transformer($payload);
    }
    
    return static::deserialize($payload);
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