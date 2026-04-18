<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\SetShareExpiration;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class SetShareExpirationCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    private ?\DateTimeImmutable $expiresAt = null,
  ) {}

  public function getExpiresAt(): ?DateTime {
    if ($this->expiresAt === null) {
      return null;
    }

    return DateTime::fromDateTimeImmutable($this->expiresAt);
  }
}
