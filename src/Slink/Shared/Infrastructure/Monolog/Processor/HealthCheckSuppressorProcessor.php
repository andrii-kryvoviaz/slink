<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Monolog\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('monolog.processor')]
class HealthCheckSuppressorProcessor implements ProcessorInterface
{
  /**
   * @param LogRecord $record
   * @return LogRecord|null
   */
  public function __invoke(LogRecord $record): ?LogRecord
  {
    if ($this->isRoute($record, 'health_check')) {
      return null;
    }
    
    return $record;
  }
  
  /**
   * @param LogRecord $record
   * @return string|null
   */
  private function getRoute(LogRecord $record): ?string
  {
    $recordData = $record->toArray();
    
    return $recordData['context']['route'] ?? null;
  }
  
  /**
   * @param LogRecord $record
   * @param string $route
   * @return bool
   */
  private function isRoute(LogRecord $record, string $route): bool
  {
    return $this->getRoute($record) === $route;
  }
}