<?php

declare(strict_types=1);

namespace Slink\Tag\Infrastructure\EventConsumer;

use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Infrastructure\MessageBus\Event\AbstractEventConsumer;
use Slink\Tag\Application\Command\DeleteTag\DeleteTagCommand;
use Slink\Tag\Domain\Event\TagWasDeleted;

final class TagCascadeDeleteConsumer extends AbstractEventConsumer {
  public function __construct(
    private readonly CommandBusInterface $commandBus,
  ) {
  }

  public function handleTagWasDeleted(TagWasDeleted $event): void {
    foreach ($event->directChildIds as $childId) {
      $command = new DeleteTagCommand($childId);
      $this->commandBus->handle($command->withContext(['userId' => $event->userId]));
    }
  }
}
