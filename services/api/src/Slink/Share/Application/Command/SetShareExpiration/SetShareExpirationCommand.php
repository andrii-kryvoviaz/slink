<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\SetShareExpiration;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SetShareExpirationCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\Date]
    private ?string $expiresAt = null,
  ) {}

  /**
   * @throws DateTimeException
   */
  public function getExpiresAt(): ?DateTime {
    if ($this->expiresAt === null) {
      return null;
    }

    return DateTime::fromString($this->expiresAt . 'T23:59:59+00:00');
  }
}
