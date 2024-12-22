<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\EventStore\Upcaster;

use Slink\Settings\Domain\Event\SettingsChanged;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractUpcaster;

class SettingsChangedUpcaster extends AbstractUpcaster {
  
  /**
   * @inheritDoc
   */
  public function upcast(array $message): array {
    if (!$this->isEventType($message, SettingsChanged::class)) {
      return $message;
    }
    
    if (!isset($message['payload']['allowRegistration'])) {
      $message['payload']['allowRegistration'] = true;
    }
    
    return $message;
  }
}