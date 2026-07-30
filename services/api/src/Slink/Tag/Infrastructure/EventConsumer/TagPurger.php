<?php

declare(strict_types=1);

namespace Slink\Tag\Infrastructure\EventConsumer;

use Slink\Shared\Infrastructure\MessageBus\Event\AbstractEventConsumer;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\User\Domain\Event\UserWasPurged;

final class TagPurger extends AbstractEventConsumer {
  public function __construct(
    private readonly TagRepositoryInterface $tagRepository,
  ) {
  }

  public function handleUserWasPurged(UserWasPurged $event): void {
    $this->tagRepository->deleteByUserId($event->id);
  }
}
