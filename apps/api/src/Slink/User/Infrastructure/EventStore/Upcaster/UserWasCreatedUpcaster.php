<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\EventStore\Upcaster;

use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractUpcaster;
use Slink\User\Domain\Event\UserWasCreated;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Username;

class UserWasCreatedUpcaster extends AbstractUpcaster {
  
  public function upcast(array $message): array {
    if (!$this->isEventType($message, UserWasCreated::class)) {
      return $message;
    }
    
    if (!isset($message['payload']['username']) && isset($message['payload']['displayName'])) {
      $displayName = DisplayName::fromString($message['payload']['displayName']);
      $username = Username::fromDisplayName($displayName);
      
      $message['payload']['username'] = $username->toString();
    }
    
    return $message;
  }
}