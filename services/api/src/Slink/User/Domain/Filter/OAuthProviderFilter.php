<?php

declare(strict_types=1);

namespace Slink\User\Domain\Filter;

use Slink\User\Application\Query\GetOAuthProviders\GetOAuthProvidersQuery;

final readonly class OAuthProviderFilter {
  public function __construct(
    private bool $enabledOnly = true,
  ) {
  }

  public static function fromQuery(GetOAuthProvidersQuery $query): self {
    return new self(
      enabledOnly: $query->isEnabledOnly(),
    );
  }

  public function isEnabledOnly(): bool {
    return $this->enabledOnly;
  }
}
