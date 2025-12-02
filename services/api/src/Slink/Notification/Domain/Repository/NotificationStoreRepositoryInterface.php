<?php

declare(strict_types=1);

namespace Slink\Notification\Domain\Repository;

use Slink\Notification\Domain\Notification;
use Slink\Shared\Domain\ValueObject\ID;

interface NotificationStoreRepositoryInterface {
  public function store(Notification $notification): void;

  public function get(ID $id): Notification;
}
