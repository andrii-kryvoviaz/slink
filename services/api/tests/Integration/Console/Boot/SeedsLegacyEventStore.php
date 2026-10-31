<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use Symfony\Component\Filesystem\Filesystem;

trait SeedsLegacyEventStore {
  private ?string $legacyStoreDirectory = null;

  private function legacyStorePath(): string {
    if ($this->legacyStoreDirectory === null) {
      $this->legacyStoreDirectory = \sys_get_temp_dir() . '/slink-legacy-store-' . \bin2hex(\random_bytes(6));
      \mkdir($this->legacyStoreDirectory);
    }

    return $this->legacyStoreDirectory . '/slink_store.db';
  }

  private function legacyStoreUrl(): string {
    return 'sqlite:///' . $this->legacyStorePath();
  }

  private function seedLegacyStore(): void {
    $this->legacyStore()->exec(<<<'SQL'
      CREATE TABLE event_store (
        id SERIAL PRIMARY KEY,
        event_id UUID NOT NULL,
        aggregate_root_id UUID NOT NULL,
        version INT NULL,
        payload varchar(16001) NOT NULL,
        CONSTRAINT reconstitution UNIQUE (aggregate_root_id, version)
      );
      CREATE TABLE snapshots (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        aggregate_root_id VARCHAR(36) NOT NULL UNIQUE,
        aggregate_version INTEGER NOT NULL,
        state TEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
      );
      CREATE TABLE messenger_messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        body CLOB NOT NULL,
        headers CLOB NOT NULL,
        queue_name VARCHAR(190) NOT NULL,
        created_at DATETIME NOT NULL,
        available_at DATETIME NOT NULL,
        delivered_at DATETIME DEFAULT NULL
      );
      INSERT INTO snapshots (aggregate_root_id, aggregate_version, state)
        VALUES ('11111111-2222-4333-8444-555555555555', 50, '{"kept":true}');
      INSERT INTO messenger_messages (body, headers, queue_name, created_at, available_at)
        VALUES ('{}', '{}', 'failed', '2026-01-02 03:04:05', '2026-01-02 03:04:05');
      SQL);
  }

  private function recordLegacyRoleEvent(string $event, string $userId, string $role, int $version): void {
    $statement = $this->legacyStore()->prepare(
      'INSERT INTO event_store (event_id, aggregate_root_id, version, payload) VALUES (?, ?, ?, ?)',
    );
    self::assertNotFalse($statement);
    $statement->execute([
      \sprintf('00000000-0000-4000-8000-%012d', $version),
      $userId,
      $version,
      \json_encode([
        'headers' => [
          '__event_type' => $event,
          '__aggregate_root_type' => 'slink.user.domain.user',
          '__aggregate_root_id' => $userId,
          '__aggregate_root_version' => $version,
        ],
        'payload' => ['uuid' => $userId, 'role' => $role],
      ], JSON_THROW_ON_ERROR),
    ]);
  }

  /**
   * @return array<string, string>
   */
  private function legacyStoreFiles(): array {
    $files = [];
    $directory = \dirname($this->legacyStorePath());

    $names = \scandir($directory);
    self::assertIsArray($names);

    foreach (\array_diff($names, ['.', '..']) as $name) {
      $files[$name] = (string) \hash_file('sha256', $directory . '/' . $name);
    }

    return $files;
  }

  private function removeLegacyStore(): void {
    if ($this->legacyStoreDirectory === null) {
      return;
    }

    new Filesystem()->remove($this->legacyStoreDirectory);
    $this->legacyStoreDirectory = null;
  }

  private function legacyStore(): \PDO {
    return new \PDO('sqlite:' . $this->legacyStorePath(), options: [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
  }
}
