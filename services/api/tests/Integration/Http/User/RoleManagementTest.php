<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use PHPUnit\Framework\Attributes\Test;
use Slink\User\Domain\Enum\UserRole;
use Tests\Integration\Http\HttpTestCase;

final class RoleManagementTest extends HttpTestCase {
  private function grantRole(string $token, string $userId, string $role): int {
    return $this->apiRequest(
      'POST',
      '/api/user/role',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $userId, 'role' => $role], JSON_THROW_ON_ERROR),
    );
  }

  private function revokeRole(string $token, string $userId, string $role): int {
    return $this->apiRequest(
      'DELETE',
      '/api/user/role',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $userId, 'role' => $role], JSON_THROW_ON_ERROR),
    );
  }

  /**
   * @return array<int, string>
   */
  private function rolesFromToken(string $token): array {
    $parts = \explode('.', $token);
    self::assertCount(3, $parts, 'JWT must have three segments.');

    $decoded = \base64_decode(\strtr($parts[1], '-_', '+/'), true);
    self::assertIsString($decoded, 'JWT payload must be base64-decodable.');

    /** @var array{roles?: array<int, string>} $payload */
    $payload = \json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);

    return $payload['roles'] ?? [];
  }

  #[Test]
  public function nonAdminCannotGrantRole(): void {
    $this->createUser('role-nonadmin@local.test', 'rolenonadmin', self::PASSWORD);
    $targetUserId = $this->createUser('role-target@local.test', 'roletarget', self::PASSWORD);

    $nonAdminToken = $this->login('rolenonadmin', self::PASSWORD);

    self::assertSame(
      403,
      $this->grantRole($nonAdminToken, $targetUserId, UserRole::Admin->value),
      'A non-admin must not be able to grant roles.',
    );

    $targetUserToken = $this->login('roletarget', self::PASSWORD);
    self::assertNotContains(
      UserRole::Admin->value,
      $this->rolesFromToken($targetUserToken),
      'No privilege escalation must have occurred.',
    );
  }

  #[Test]
  public function adminCanGrantAndRevokeAdminRole(): void {
    $adminId = $this->createUser('role-admin@local.test', 'roleadmin', self::PASSWORD);
    $this->grantAdmin($adminId);
    $adminToken = $this->login('roleadmin', self::PASSWORD);

    $targetId = $this->createUser('role-target@local.test', 'roletarget', self::PASSWORD);

    self::assertNotContains(
      UserRole::Admin->value,
      $this->rolesFromToken($this->login('roletarget', self::PASSWORD)),
      'Precondition: the target must not start as admin.',
    );

    self::assertContains(
      $this->grantRole($adminToken, $targetId, UserRole::Admin->value),
      [200, 201],
      'Admin grant should succeed.',
    );

    $grantedToken = $this->login('roletarget', self::PASSWORD);
    self::assertContains(
      UserRole::Admin->value,
      $this->rolesFromToken($grantedToken),
      'Fresh login JWT must carry ROLE_ADMIN after a grant.',
    );
    self::assertSame(
      200,
      $this->apiRequest('GET', '/api/users/1', $grantedToken),
      'Admin-only endpoint must succeed for the freshly promoted user.',
    );

    self::assertContains(
      $this->revokeRole($adminToken, $targetId, UserRole::Admin->value),
      [200, 204],
      'Admin revoke should succeed.',
    );

    $revokedToken = $this->login('roletarget', self::PASSWORD);
    self::assertNotContains(
      UserRole::Admin->value,
      $this->rolesFromToken($revokedToken),
      'Fresh login JWT must no longer carry ROLE_ADMIN after a revoke.',
    );
    self::assertSame(
      403,
      $this->apiRequest('GET', '/api/users/1', $revokedToken),
      'Admin-only endpoint must be forbidden after the role is revoked.',
    );
  }
}
