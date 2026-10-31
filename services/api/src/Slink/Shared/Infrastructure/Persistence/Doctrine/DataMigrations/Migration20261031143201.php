<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\DataMigrations;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Slink\Shared\Domain\ValueObject\EscapedString;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationInterface;
use Slink\User\Domain\Enum\UserStatus;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class Migration20261031143201 implements DataMigrationInterface {
  private const string ADMIN_ROLE = 'ROLE_ADMIN';

  public function __construct(
    #[\SensitiveParameter]
    #[Autowire('%env(default::ADMIN_USERNAME)%')]
    private readonly ?string $adminUsername,
    #[\SensitiveParameter]
    #[Autowire('%env(default::ADMIN_EMAIL)%')]
    private readonly ?string $adminEmail,
    private readonly Connection $connection,
    private readonly LoggerInterface $logger,
  ) {}

  public function up(): void {
    $this->restoreAdminRole();
    $this->warnWhenNoAdminRemains();
  }

  public function down(): void {}

  public function getDescription(): string {
    return 'Restore the admin role row of the user created from ADMIN_USERNAME and ADMIN_EMAIL';
  }

  private function restoreAdminRole(): void {
    $this->connection->executeStatement(
      'INSERT INTO user_to_role (user_id, role)
       SELECT uuid, :role FROM "user"
       WHERE username = :username
         AND email = :email
         AND status != :deleted
         AND EXISTS (SELECT 1 FROM user_role WHERE role = :role)
         AND NOT EXISTS (SELECT 1 FROM user_to_role WHERE user_id = "user".uuid AND role = :role)',
      [
        'role' => self::ADMIN_ROLE,
        'username' => EscapedString::fromString($this->adminUsername ?: 'admin')->getValue(),
        'email' => (string) $this->adminEmail,
        'deleted' => UserStatus::Deleted->value,
      ],
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
