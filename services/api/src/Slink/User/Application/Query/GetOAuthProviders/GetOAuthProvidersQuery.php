<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\GetOAuthProviders;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetOAuthProvidersQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private bool $enabledOnly = true,
  ) {
  }

  public function isEnabledOnly(): bool {
    return $this->enabledOnly;
  }
}
