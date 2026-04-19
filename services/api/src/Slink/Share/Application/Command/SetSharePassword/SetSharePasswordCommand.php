<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\SetSharePassword;

use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class SetSharePasswordCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[\SensitiveParameter]
    private ?string $password = null,
  ) {}

  public function getHashedPassword(): ?HashedSharePassword {
    if ($this->password === null) {
      return null;
    }

    return HashedSharePassword::encode($this->password);
  }
}
