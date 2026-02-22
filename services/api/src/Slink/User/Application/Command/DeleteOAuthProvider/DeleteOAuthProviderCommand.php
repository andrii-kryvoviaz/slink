<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\DeleteOAuthProvider;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class DeleteOAuthProviderCommand implements CommandInterface {
  use EnvelopedMessage;
}
