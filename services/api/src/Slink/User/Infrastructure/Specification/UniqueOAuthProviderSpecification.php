<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Specification;

use Slink\User\Domain\Exception\DuplicateOAuthProviderException;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\Specification\UniqueOAuthProviderSpecificationInterface;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;

final readonly class UniqueOAuthProviderSpecification implements UniqueOAuthProviderSpecificationInterface {
  public function __construct(private OAuthProviderRepositoryInterface $providerRepository) {}

  public function ensureUnique(ProviderSlug $slug): void {
    $existingProvider = $this->providerRepository->findByProvider($slug);
    if ($existingProvider !== null) {
      throw new DuplicateOAuthProviderException($slug->toString());
    }
  }
}
