<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateAdminUser;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class CreateAdminUserCommand implements CommandInterface {
  use EnvelopedMessage;
}
