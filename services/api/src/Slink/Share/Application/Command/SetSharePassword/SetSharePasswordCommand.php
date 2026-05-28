<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\SetSharePassword;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class SetSharePasswordCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[\SensitiveParameter]
    private ?string $password = null,
  ) {}

  public function getPassword(): ?string {
    return $this->password;
  }
}
