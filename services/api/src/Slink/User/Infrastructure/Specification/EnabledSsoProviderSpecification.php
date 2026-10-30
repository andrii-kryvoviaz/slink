<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Specification;

use Slink\User\Domain\Filter\OAuthProviderFilter;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\Specification\EnabledSsoProviderSpecificationInterface;

final readonly class EnabledSsoProviderSpecification implements EnabledSsoProviderSpecificationInterface {
  public function __construct(private OAuthProviderRepositoryInterface $providerRepository) {}

  public function isSatisfied(): bool {
    return $this->providerRepository->getProviders(new OAuthProviderFilter(enabledOnly: true)) !== [];
  }
}
