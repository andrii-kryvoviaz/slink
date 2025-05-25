<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\ReadModel;

use Slink\Shared\Infrastructure\MessageBus\Event\AbstractEventConsumer;

abstract class AbstractProjection extends AbstractEventConsumer {
}