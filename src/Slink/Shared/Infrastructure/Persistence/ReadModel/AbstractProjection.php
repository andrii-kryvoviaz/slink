<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\ReadModel;

use EventSauce\EventSourcing\EventConsumption\EventConsumer;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['event_sauce.event_consumer'])]
class AbstractProjection extends EventConsumer {
}