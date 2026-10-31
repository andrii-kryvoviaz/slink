<?php

declare(strict_types=1);

namespace Slink\Notification\Infrastructure\Repository;

use EventSauce\EventSourcing\MessageDispatcher;
use Slink\Notification\Domain\Notification;
use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Notification\Domain\Repository\NotificationStoreRepositoryInterface;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractViewBackedStore;

/**
 * @extends AbstractViewBackedStore<Notification, NotificationView>
 */
final class NotificationStoreRepository extends AbstractViewBackedStore implements NotificationStoreRepositoryInterface {
  public function __construct(
    MessageDispatcher $messageDispatcher,
    private readonly NotificationRepositoryInterface $notificationRepository,
  ) {
    parent::__construct($messageDispatcher);
  }

  protected static function getAggregateRootClass(): string {
    return Notification::class;
  }

  protected function findRootView(ID $id): object {
    return $this->notificationRepository->oneById($id->toString());
  }

  protected function hydrate(object $view): object {
    return Notification::fromView($view);
  }

  public function store(Notification $notification): void {
    $this->persist($notification);
  }

  public function get(ID $id): Notification {
    return $this->retrieve($id);
  }
}
