<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\MoveOAuthProvider;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Enum\SortDirection;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Exception\OAuthProviderNotFoundException;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;

final readonly class MoveOAuthProviderHandler implements CommandHandlerInterface {
  public function __construct(
    private OAuthProviderStoreRepositoryInterface $providerStore,
    private OAuthProviderRepositoryInterface $repository,
  ) {}

  public function __invoke(MoveOAuthProviderCommand $command): void {
    $direction = SortDirection::from($command->getDirection());

    $target = $this->repository->findById(ID::fromString($command->getId()));

    if ($target === null) {
      throw new OAuthProviderNotFoundException($command->getId());
    }

    $neighbor = $this->repository->findNeighbor($target->getSortOrder(), $direction);

    if ($neighbor === null) {
      return;
    }

    $targetAggregate = $this->providerStore->get(ID::fromString($target->getId()));
    $neighborAggregate = $this->providerStore->get(ID::fromString($neighbor->getId()));

    $targetAggregate->update(sortOrder: $neighbor->getSortOrder());
    $neighborAggregate->update(sortOrder: $target->getSortOrder());

    $this->providerStore->store($targetAggregate);
    $this->providerStore->store($neighborAggregate);
  }
}
