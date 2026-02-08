<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\EventStore;

use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\Upcasting\UpcasterChain;
use EventSauce\EventSourcing\Upcasting\UpcastingMessageSerializer;
use Slink\User\Infrastructure\EventStore\Upcaster\ApiKeyWasCreatedUpcaster;
use Slink\User\Infrastructure\EventStore\Upcaster\UserWasCreatedUpcaster;

final readonly class UpcastingMessageSerializerFactory {
  public function __construct(
    private MessageSerializer $messageSerializer
  ) {
  }
  
  public function createSerializer(): UpcastingMessageSerializer {
    $upcasters = [
      new UserWasCreatedUpcaster(),
      new ApiKeyWasCreatedUpcaster()
    ];
    
    return new UpcastingMessageSerializer(
      $this->messageSerializer,
      new UpcasterChain(...$upcasters)
    );
  }
}