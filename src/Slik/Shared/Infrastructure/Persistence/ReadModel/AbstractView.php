<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\Persistence\ReadModel;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

abstract readonly class AbstractView {
  public static function fromEvent(SerializablePayload $event): static {
    return static::deserialize($event->toPayload());
  }

  abstract public static function deserialize(array $payload): static;
}