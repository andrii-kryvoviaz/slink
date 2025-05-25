<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\EventStore;

use EventSauce\EventSourcing\Upcasting\Upcaster;

abstract class AbstractUpcaster implements Upcaster {
  
  /**
   * @param array<string, array<string, mixed>> $message
   * @return array<string, array<string, mixed>>
   */
  public abstract function upcast(array $message): array;
  
  /**
   * @param array<string, mixed> $message
   * @param class-string $eventType
   * @return bool
   */
  protected function isEventType(array $message, string $eventType): bool {
    return $message['headers']['__event_type'] === $this->getTypeFromClassName($eventType);
  }
  
  /**
   * @param array<string, mixed> $message
   * @param class-string $aggregateRootType
   * @return bool
   */
  protected function isAggregateRootType(array $message, string $aggregateRootType): bool {
    return $message['headers']['__aggregate_root_type'] === $this->getTypeFromClassName($aggregateRootType);
  }
  
  /**
   * @param class-string $className
   * @return string|null
   */
  private function getTypeFromClassName(string $className): ?string {
    $classNameWithDots = str_replace('\\', '.', $className);
    $snakeCasedName = preg_replace('/([a-z])([A-Z])/', '$1_$2', $classNameWithDots);
    return $snakeCasedName ? strtolower($snakeCasedName) : null;
  }
}