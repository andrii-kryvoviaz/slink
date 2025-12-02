<?php

declare(strict_types=1);

namespace Slink\Notification\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\EntityManagerInterface;
use Slink\Notification\Domain\Event\NotificationWasCreated;
use Slink\Notification\Domain\Event\NotificationWasRead;
use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;
use Slink\Shared\Domain\Event\EventWithEntityManager;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;

final class NotificationProjection extends AbstractProjection {
  public function __construct(
    private readonly NotificationRepositoryInterface $repository,
    private readonly EntityManagerInterface $em,
  ) {
  }

  public function handleNotificationWasCreated(NotificationWasCreated $event): void {
    $eventWithEntityManager = EventWithEntityManager::decorate($event, $this->em);
    $notification = NotificationView::fromEvent($eventWithEntityManager);

    $this->repository->add($notification);
  }

  public function handleNotificationWasRead(NotificationWasRead $event): void {
    $notification = $this->repository->oneById($event->id->toString());
    $notification->markAsRead($event->readAt);

    $this->em->flush();
  }
}
