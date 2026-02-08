<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\EventStore\Upcaster;

use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractUpcaster;
use Slink\User\Domain\Event\ApiKeyWasCreated;

class ApiKeyWasCreatedUpcaster extends AbstractUpcaster {

  public function upcast(array $message): array {
    if (!$this->isEventType($message, ApiKeyWasCreated::class)) {
      return $message;
    }

    if (isset($message['payload']['key']) && !isset($message['payload']['keyHash'])) {
      $message['payload']['keyHash'] = hash('sha256', $message['payload']['key']);
      unset($message['payload']['key']);
    }

    return $message;
  }
}
