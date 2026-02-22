<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateOAuthProvider;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\OAuthProvider as OAuthProviderEnum;
use Slink\User\Domain\OAuthProvider;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;
use Slink\User\Domain\Specification\UniqueOAuthProviderSpecificationInterface;

final readonly class CreateOAuthProviderHandler implements CommandHandlerInterface {
  public function __construct(
    private OAuthProviderStoreRepositoryInterface $providerStore,
    private UniqueOAuthProviderSpecificationInterface $uniqueOAuthProviderSpecification,
  ) {}

  public function __invoke(CreateOAuthProviderCommand $command): string {
    $this->uniqueOAuthProviderSpecification->ensureUnique(OAuthProviderEnum::from($command->getSlug()));

    $id = ID::generate();

    $provider = OAuthProvider::create(
      id: $id,
      name: $command->getName(),
      slug: $command->getSlug(),
      type: $command->getType(),
      clientId: $command->getClientId(),
      clientSecret: $command->getClientSecret(),
      discoveryUrl: $command->getDiscoveryUrl(),
      scopes: $command->getScopes(),
      enabled: $command->isEnabled(),
    );

    $this->providerStore->store($provider);

    return $id->toString();
  }
}
