<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Settings;

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

  private function entityManager(): EntityManagerInterface {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager;
  }
}
