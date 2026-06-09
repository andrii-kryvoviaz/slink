<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\EventConsumer;

use Slink\Collection\Domain\Event\CollectionWasDeleted;
use Slink\Image\Domain\Event\ImageWasDeleted;
use Slink\Share\Application\Command\RevokeShare\RevokeShareCommand;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Infrastructure\MessageBus\Event\AbstractEventConsumer;

final class ShareRevoker extends AbstractEventConsumer {
  public function __construct(
    private readonly CommandBusInterface $commandBus,
    private readonly ShareRepositoryInterface $shareRepository,
  ) {
  }

  public function handleImageWasDeleted(ImageWasDeleted $event): void {
    $this->revokeShares($event->id->toString(), ShareableType::Image);
  }

  public function handleCollectionWasDeleted(CollectionWasDeleted $event): void {
    $this->revokeShares($event->id->toString(), ShareableType::Collection);
  }

  private function revokeShares(string $shareableId, ShareableType $shareableType): void {
    foreach ($this->shareRepository->findAllByShareable($shareableId, $shareableType) as $share) {
      $this->commandBus->handle(new RevokeShareCommand($share->getId()));
    }
  }
}
