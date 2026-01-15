<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\GetUserPreferences;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetUserPreferencesQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $userId,
  ) {
  }

  public function getUserId(): string {
    return $this->userId;
  }
}
