<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateOAuthProvider;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;

final readonly class UpdateOAuthProviderHandler implements CommandHandlerInterface {
  public function __construct(
    private OAuthProviderStoreRepositoryInterface $providerStore,
  ) {}

  public function __invoke(UpdateOAuthProviderCommand $command, string $id): void {
    $provider = $this->providerStore->get(ID::fromString($id));

    $provider->update(
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
  }
}
