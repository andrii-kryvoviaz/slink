<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\DataMigrations;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\DsnParser;
use Psr\Log\LoggerInterface;
use Slink\Shared\Domain\ValueObject\EscapedString;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationInterface;
use Slink\User\Domain\Enum\UserStatus;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class Migration20261031143201 implements DataMigrationInterface {
  private const string ADMIN_ROLE = 'ROLE_ADMIN';
  private const string GRANTED = 'slink.user.domain.event.role.user_granted_role';
  private const string REVOKED = 'slink.user.domain.event.role.user_revoked_role';
  private const string STORE_URL = 'sqlite:////app/var/data/slink_store.db';

  public function __construct(
    #[\SensitiveParameter]
    #[Autowire('%env(default::ADMIN_USERNAME)%')]
    private readonly ?string $adminUsername,
    #[\SensitiveParameter]
    #[Autowire('%env(default::ADMIN_EMAIL)%')]
    private readonly ?string $adminEmail,
    #[\SensitiveParameter]
    #[Autowire('%env(default::resolve:ES_DATABASE_URL)%')]
    private readonly ?string $storeUrl,
    private readonly Connection $connection,
    private readonly LoggerInterface $logger,
  ) {}

  public function up(): void {
    $history = $this->readHistory();
    $this->restoreGranted($history);
    $this->restoreAdminRole(\array_keys(\array_filter($history ?? [], static fn (bool $granted): bool => !$granted)));
    $this->warnWhenNoAdminRemains();
  }

  public function down(): void {}

  public function getDescription(): string {
    return 'Restore the admin role rows held in the old event store and of the user named by ADMIN_USERNAME and ADMIN_EMAIL';
  }

  /**
   * @return array<string, bool>|null
   */
  private function readHistory(): ?array {
    try {
      return $this->storeHistory();
    } catch (\Throwable $exception) {
      $this->logger->warning('The old event store could not be read: ' . $exception->getMessage());

      return null;
    }
  }

  /**
   * @param array<string, bool>|null $history
   */
  private function restoreGranted(?array $history): void {
    $granted = \array_keys(\array_filter($history ?? []));
    $this->insert('uuid IN (:granted)', ['granted' => $granted], ['granted' => ArrayParameterType::STRING]);
  }

  /**
   * @return array<string, bool>|null
   */
  private function storeHistory(): ?array {
    $path = $this->storePath();

    if ($path === null || !\is_file($path)) {
      return null;
    }

    $store = new \Pdo\Sqlite('sqlite:' . $path, null, null, [
      \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
      \Pdo\Sqlite::ATTR_OPEN_FLAGS => \Pdo\Sqlite::OPEN_READONLY,
    ]);
    $statement = $store->prepare(
      "SELECT aggregate_root_id, json_extract(payload, '$.headers.__event_type') AS type
       FROM event_store
       WHERE json_extract(payload, '$.headers.__event_type') IN (:granted, :revoked)
         AND json_extract(payload, '$.payload.role') = :role
       ORDER BY version ASC",
    );
    $statement->execute(['granted' => self::GRANTED, 'revoked' => self::REVOKED, 'role' => self::ADMIN_ROLE]);

    $last = [];
    foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
      $last[(string) $row['aggregate_root_id']] = $row['type'] === self::GRANTED;
    }

    return $last;
  }

  private function storePath(): ?string {
    $params = new DsnParser()->parse($this->storeUrl ?: self::STORE_URL);

    if (!empty($params['memory'])) {
      return null;
    }

    $path = $params['path'] ?? null;
    if (!\is_string($path) || $path === '') {
      return null;
    }

    return $path;
  }

  /**
   * @param list<string> $revoked
   */
  private function restoreAdminRole(array $revoked): void {
    $filter = 'username = :username AND email = :email';
    $params = [
      'username' => EscapedString::fromString($this->adminUsername ?: 'admin')->getValue(),
      'email' => (string) $this->adminEmail,
    ];
    $types = [];

    if ($revoked !== []) {
      $filter .= ' AND uuid NOT IN (:revoked)';
      $params['revoked'] = $revoked;
      $types['revoked'] = ArrayParameterType::STRING;
    }

    $this->insert($filter, $params, $types);
  }

  /**
   * @param array<string, mixed> $params
   * @param array<string, ArrayParameterType> $types
   */
  private function insert(string $filter, array $params, array $types): void {
    $this->connection->executeStatement(
      'INSERT INTO user_to_role (user_id, role)
       SELECT uuid, :role FROM "user"
       WHERE ' . $filter . '
         AND status != :deleted
         AND EXISTS (SELECT 1 FROM user_role WHERE role = :role)
         AND NOT EXISTS (SELECT 1 FROM user_to_role WHERE user_id = "user".uuid AND role = :role)',
      [...$params, 'role' => self::ADMIN_ROLE, 'deleted' => UserStatus::Deleted->value],
      $types,
    );
  }

  private function warnWhenNoAdminRemains(): void {
    if (!$this->connection->fetchOne('SELECT 1 FROM "user" LIMIT 1')) {
      return;
    }

    if ($this->connection->fetchOne('SELECT 1 FROM user_to_role WHERE role = :role LIMIT 1', ['role' => self::ADMIN_ROLE])) {
      return;
    }

    $this->logger->warning('No user holds the admin role. Recover with: slink user:grant:role --email=<email> ' . self::ADMIN_ROLE);
  }
}
