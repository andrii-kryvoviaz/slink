<?php

declare(strict_types=1);

namespace Slink\Notification\Infrastructure\Repository;

use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;
use Slink\Notification\Domain\Notification;
use Slink\Notification\Domain\Repository\NotificationStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractStoreRepository;

final class NotificationStoreRepository extends AbstractStoreRepository implements NotificationStoreRepositoryInterface {
  public static function getAggregateRootClass(): string {
    return Notification::class;
  }

  public function __construct(
    MessageRepository $messageRepository,
    MessageDispatcher $messageDispatcher
  ) {
    parent::__construct($messageRepository, $messageDispatcher);
  }

  public function store(Notification $notification): void {
    $this->persist($notification);
  }

  /**
   * @return Notification
   */
  public function get(ID $id): Notification {
    /** @var Notification */
    return $this->retrieve($id);
  }
}
