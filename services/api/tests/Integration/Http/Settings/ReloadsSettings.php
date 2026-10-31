<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Settings;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Slink\Settings\Domain\Repository\SettingStoreRepositoryInterface;
use Slink\Settings\Domain\Settings;
use Slink\Settings\Domain\ValueObject\Storage\AmazonS3StorageSettings;
use Slink\Settings\Infrastructure\Provider\DatabaseProvider;

trait ReloadsSettings {
  private function reloadSettings(): Settings {
    $this->entityManager()->clear();

    /** @var SettingStoreRepositoryInterface $store */
    $store = static::getContainer()->get(SettingStoreRepositoryInterface::class);

    return $store->get();
  }

  private function reloadedS3Settings(): AmazonS3StorageSettings {
    /** @var array<string, mixed> $payload */
    $payload = $this->reloadSettings()->get('storage.adapter.s3');

    return AmazonS3StorageSettings::fromPayload($payload);
  }

  private function decryptedRow(string $key): mixed {
    $this->entityManager()->clear();

    /** @var DatabaseProvider $provider */
    $provider = static::getContainer()->get(DatabaseProvider::class);

    return $provider->get($key);
  }

  private function rawRow(string $key): string {
    $value = $this->entityManager()->getConnection()->fetchOne('SELECT value FROM "settings" WHERE "key" = ?', [$key]);
    self::assertIsString($value, \sprintf('Row "%s" must exist.', $key));

    return $value;
  }

  /**
   * @return array{events: int, snapshots: int}
   */
  private function settingsHistory(): array {
    return [
      'events' => (int) $this->eventStore()->fetchOne(
        'SELECT COUNT(*) FROM event_store WHERE aggregate_root_id = ?',
        [Settings::getIdReference()],
      ),
      'snapshots' => (int) $this->eventStore()->fetchOne(
        'SELECT COUNT(*) FROM snapshots WHERE aggregate_root_id = ?',
        [Settings::getIdReference()],
      ),
    ];
  }

  private function forgetSettingsHistory(): void {
    foreach (['event_store', 'snapshots'] as $table) {
      $this->eventStore()->executeStatement(
        \sprintf('DELETE FROM %s WHERE aggregate_root_id = ?', $table),
        [Settings::getIdReference()],
      );
    }
  }

  private function entityManager(): EntityManagerInterface {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager;
  }

  private function eventStore(): Connection {
    /** @var Connection $eventStore */
    $eventStore = static::getContainer()->get('doctrine.dbal.event_store_connection');

    return $eventStore;
  }
}
