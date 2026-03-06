<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\GetOAuthProviders;

use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\User\Domain\Filter\OAuthProviderFilter;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;

final readonly class GetOAuthProvidersHandler implements QueryHandlerInterface {
  public function __construct(
    private OAuthProviderRepositoryInterface $providerRepository,
  ) {
  }

  /**
   * @param array<string> $groups
   * @return array<Item>
   */
  public function __invoke(GetOAuthProvidersQuery $query, array $groups = ['public']): array {
    $providers = $this->providerRepository->getProviders(
      OAuthProviderFilter::fromQuery($query),
    );

    return array_map(
      fn($provider) => Item::fromEntity($provider, groups: $groups),
      $providers,
    );
  }
}
