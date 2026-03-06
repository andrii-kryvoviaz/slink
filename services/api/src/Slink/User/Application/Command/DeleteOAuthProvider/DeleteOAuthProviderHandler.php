<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\DeleteOAuthProvider;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;

final readonly class DeleteOAuthProviderHandler implements CommandHandlerInterface {
  public function __construct(
    private OAuthProviderStoreRepositoryInterface $providerStore,
  ) {}

  public function __invoke(DeleteOAuthProviderCommand $command): void {
    $provider = $this->providerStore->get(ID::fromString($command->getId()));

    $provider->remove();

    $this->providerStore->store($provider);
  }
}
