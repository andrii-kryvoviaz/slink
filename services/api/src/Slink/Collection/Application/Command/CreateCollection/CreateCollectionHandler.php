<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\CreateCollection;

use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Collection\Domain\Service\UniqueCollectionNameGeneratorInterface;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CreateCollectionHandler implements CommandHandlerInterface {
  public function __construct(
    private CollectionStoreRepositoryInterface $collectionStore,
    private UniqueCollectionNameGeneratorInterface $nameGenerator,
  ) {
  }

  public function __invoke(CreateCollectionCommand $command, string $userId): void {
    $userIdValue = ID::fromString($userId);
    $baseName = CollectionName::fromString($command->getName());
    $uniqueName = $this->nameGenerator->generate($baseName, $userIdValue);

    $collection = Collection::create(
      $command->getId(),
      $userIdValue,
      $uniqueName,
      CollectionDescription::fromString($command->getDescription()),
    );

    $this->collectionStore->store($collection);
  }
}
