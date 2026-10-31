<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\DataMigrations;

use Doctrine\DBAL\Connection;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationInterface;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Event\Role\UserGrantedRole;
use Slink\User\Domain\Event\Role\UserRevokedRole;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class Migration20261031143201 implements DataMigrationInterface {
  public function __construct(
    #[Autowire(service: 'doctrine.dbal.event_store_connection')]
    private readonly Connection $eventStore,
    private readonly Connection $connection,
  ) {}

  public function up(): void {
    foreach ($this->grantedRoles() as $userId => $roles) {
      foreach (array_keys(array_filter($roles)) as $role) {
        $this->assertRoleRow((string) $userId, (string) $role);
      }
    }
  }

  public function down(): void {}

  public function getDescription(): string {
    return 'Restore role rows missing for users granted a role in the process that created them';
  }

  /**
   * @return array<string, array<string, bool>>
   */
  private function grantedRoles(): array {
    $granted = $this->storedType(UserGrantedRole::class);
    $revoked = $this->storedType(UserRevokedRole::class);

    $rows = $this->eventStore->iterateAssociative(
      'SELECT aggregate_root_id, payload FROM event_store WHERE payload LIKE ? OR payload LIKE ? ORDER BY id',
      ['%' . $granted . '%', '%' . $revoked . '%'],
    );

    $roles = [];

    foreach ($rows as $row) {
      $message = json_decode((string) $row['payload'], true, 512, JSON_THROW_ON_ERROR);
      $type = $message['headers']['__event_type'] ?? null;
      $role = $message['payload']['role'] ?? null;

      if (!is_string($role)) {
        continue;
      }

      if ($type === $granted) {
        $roles[(string) $row['aggregate_root_id']][$role] = true;
      }

      if ($type === $revoked) {
        $roles[(string) $row['aggregate_root_id']][$role] = false;
      }
    }

    return $roles;
  }

  private function storedType(string $className): string {
    $dotted = str_replace('\\', '.', $className);

    return strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1_$2', $dotted));
  }

  private function assertRoleRow(string $userId, string $role): void {
    $this->connection->executeStatement(
      'INSERT INTO user_to_role (user_id, role)
       SELECT :user, :role
       WHERE EXISTS (SELECT 1 FROM "user" WHERE uuid = :user AND status != :deleted)
         AND EXISTS (SELECT 1 FROM user_role WHERE role = :role)
         AND NOT EXISTS (SELECT 1 FROM user_to_role WHERE user_id = :user AND role = :role)',
      ['user' => $userId, 'role' => $role, 'deleted' => UserStatus::Deleted->value],
    );
  }
}
