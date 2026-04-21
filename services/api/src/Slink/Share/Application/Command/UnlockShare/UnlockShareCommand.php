<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\UnlockShare;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class UnlockShareCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[\SensitiveParameter]
    private string $password = '',
  ) {}

  public function getPassword(): string {
    return $this->password;
  }
}
