<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bridge\Doctrine\Middleware\Debug\DebugDataHolder;
use Tests\Integration\Http\HttpTestCase;

final class UserListQueryCountTest extends HttpTestCase {
  private const int LIST_QUERY_COUNT = 4;

  private function entityManager(): EntityManagerInterface {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager;
  }

  /**
   * @param array<int, string> $roles
   */
  private function assignRoles(string $userId, array $roles): void {
    foreach ($roles as $role) {
      $this->entityManager()->getConnection()->insert('user_to_role', [
        'user_id' => $userId,
        'role' => $role,
      ]);
    }
  }

  /**
   * @return array{queries: int, roleQueries: int, payload: array{meta: array{total: int}, data: array<int, array<string, mixed>>}}
   */
  private function measureUserList(string $adminToken, int $page, int $limit): array {
    /** @var DebugDataHolder $holder */
    $holder = static::getContainer()->get('doctrine.debug_data_holder');

    $this->entityManager()->clear();
    $holder->reset();

    $status = $this->apiRequest('GET', \sprintf('/api/users/%d?limit=%d', $page, $limit), $adminToken);
    self::assertSame(200, $status, 'List users failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array<int, array{sql: string}> $queries */
    $queries = $holder->getData()['read_model'] ?? [];

    $roleQueries = \array_filter(
      $queries,
      static fn(array $query): bool => \str_contains($query['sql'], 'FROM "user_role"'),
    );

    /** @var array{meta: array{total: int}, data: array<int, array<string, mixed>>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return [
      'queries' => \count($queries),
      'roleQueries' => \count($roleQueries),
      'payload' => $payload,
    ];
  }

  private function seedUsers(int $count, int $offset = 0): void {
    for ($index = $offset; $index < $offset + $count; $index++) {
      $this->createUser(\sprintf('member%d@local.test', $index), \sprintf('memberuser%d', $index), self::PASSWORD);
    }
  }

  #[Test]
  public function rolesAreHydratedWithoutAQueryPerUser(): void {
    $adminId = $this->createUser('admin@local.test', 'adminuser', self::PASSWORD);
    $this->grantAdmin($adminId);
    $adminToken = $this->login('adminuser', self::PASSWORD);

    $this->seedUsers(4);
    $this->assignRoles($adminId, ['ROLE_USER', 'ROLE_ADMIN']);
    $this->assignRoles((string) $this->entityManager()->getConnection()->fetchOne(
      'SELECT uuid FROM "user" WHERE username = ?',
      ['memberuser0'],
    ), ['ROLE_USER']);

    $fivePage = $this->measureUserList($adminToken, 1, 20);
    self::assertCount(5, $fivePage['payload']['data']);
    self::assertSame(0, $fivePage['roleQueries'], 'Roles must not be loaded by a dedicated query.');
    self::assertSame(
      self::LIST_QUERY_COUNT,
      $fivePage['queries'],
      'The user list must be served by a fixed set of queries.',
    );

    $this->seedUsers(9, 4);

    $fourteenPage = $this->measureUserList($adminToken, 1, 20);
    self::assertCount(14, $fourteenPage['payload']['data']);
    self::assertSame(
      $fivePage['queries'],
      $fourteenPage['queries'],
      'Query count must not grow with the number of users on the page.',
    );
  }

  #[Test]
  public function multiRoleUserIsSerializedWithEveryRole(): void {
    $adminId = $this->createUser('admin@local.test', 'adminuser', self::PASSWORD);
    $this->grantAdmin($adminId);
    $adminToken = $this->login('adminuser', self::PASSWORD);

    $this->assignRoles($adminId, ['ROLE_USER', 'ROLE_ADMIN']);

    $result = $this->measureUserList($adminToken, 1, 20);

    /** @var array<int, array{id: string, roles: array<int, string>}> $data */
    $data = $result['payload']['data'];
    $roles = \array_column($data, 'roles', 'id')[$adminId] ?? [];

    \sort($roles);
    self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $roles);
  }

  #[Test]
  public function paginationAndTotalsAreNotInflatedByTheRoleJoin(): void {
    $adminId = $this->createUser('admin@local.test', 'adminuser', self::PASSWORD);
    $this->grantAdmin($adminId);
    $adminToken = $this->login('adminuser', self::PASSWORD);

    $this->seedUsers(4);
    $this->assignRoles($adminId, ['ROLE_USER', 'ROLE_ADMIN']);

    $seen = [];

    foreach ([1 => 2, 2 => 2, 3 => 1] as $page => $expectedSize) {
      $result = $this->measureUserList($adminToken, $page, 2);

      self::assertCount($expectedSize, $result['payload']['data'], 'Page ' . $page . ' has the wrong size.');
      self::assertSame(5, $result['payload']['meta']['total'], 'Total must count users, not user-role rows.');

      $seen = [...$seen, ...\array_column($result['payload']['data'], 'id')];
    }

    self::assertCount(5, \array_unique($seen), 'Every user must appear exactly once across the pages.');
  }
}
