<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\MessageBus\Event;

use EventSauce\EventSourcing\EventConsumption\EventConsumer;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['event_sauce.event_consumer'])]
abstract class AbstractEventConsumer extends EventConsumer {
}