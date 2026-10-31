<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Store;

use Doctrine\DBAL\Connection;
use Slink\Shared\Domain\ValueObject\ID;

trait InspectsEventLog {
  protected function loggedEvents(ID $id): int {
    return $this->eventStoreRows('SELECT COUNT(*) FROM event_store WHERE aggregate_root_id = ?', $id);
  }

  protected function snapshots(ID $id): int {
    return $this->eventStoreRows('SELECT COUNT(*) FROM snapshots WHERE aggregate_root_id = ?', $id);
  }

  /**
   * @return array{event_store: int, snapshots: int}
   */
  protected function eventLogSize(): array {
    return [
      'event_store' => (int) $this->eventStore()->fetchOne('SELECT COUNT(*) FROM event_store'),
      'snapshots' => (int) $this->eventStore()->fetchOne('SELECT COUNT(*) FROM snapshots'),
    ];
  }

  private function eventStoreRows(string $sql, ID $id): int {
    return (int) $this->eventStore()->fetchOne($sql, [$id->toString()]);
  }

  private function eventStore(): Connection {
    /** @var Connection $connection */
    $connection = static::getContainer()->get('doctrine.dbal.event_store_connection');

    return $connection;
  }
}
