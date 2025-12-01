<?php

declare(strict_types=1);

namespace Slink\Comment\Application\Command\DeleteComment;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class DeleteCommentCommand implements CommandInterface {
  use EnvelopedMessage;
}
