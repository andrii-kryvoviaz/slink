<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Specification;

use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Exception\DuplicateOAuthProviderException;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\Specification\UniqueOAuthProviderSpecificationInterface;

final readonly class UniqueOAuthProviderSpecification implements UniqueOAuthProviderSpecificationInterface {
  public function __construct(private OAuthProviderRepositoryInterface $providerRepository) {}

  public function ensureUnique(OAuthProvider $provider): void {
    $existingProvider = $this->providerRepository->findByProvider($provider);
    if ($existingProvider !== null) {
      throw new DuplicateOAuthProviderException($provider->value);
    }
  }
}
