<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use PHPUnit\Framework\Attributes\Test;
use Slink\User\Domain\Enum\UserStatus;
use Tests\Integration\Http\HttpTestCase;

final class UserStatusChangeAuthorizationTest extends HttpTestCase {
  private function changeStatus(?string $token, string $userId, string $status): int {
    return $this->apiRequest(
      'PATCH',
      '/api/user/status',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $userId, 'status' => $status], JSON_THROW_ON_ERROR),
    );
  }

  #[Test]
  public function anonymousCannotChangeStatus(): void {
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);

    self::assertSame(401, $this->changeStatus(null, $memberId, UserStatus::Suspended->value));
  }

  #[Test]
  public function nonAdminCannotChangeStatus(): void {
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);

    self::assertSame(403, $this->changeStatus($nonOwnerToken, $memberId, UserStatus::Suspended->value));
  }

  #[Test]
  public function adminChangingStatusOfUnknownUserReturnsNotFound(): void {
    $adminToken = $this->bootAdmin();

    self::assertSame(
      404,
      $this->changeStatus($adminToken, '11111111-2222-3333-4444-555555555555', UserStatus::Suspended->value),
    );
  }

  #[Test]
  public function adminCannotChangeOwnStatus(): void {
    $adminId = $this->createUser('admin@local.test', 'adminuser', self::PASSWORD);
    $this->grantAdmin($adminId);
    $adminToken = $this->login('adminuser', self::PASSWORD);

    self::assertSame(400, $this->changeStatus($adminToken, $adminId, UserStatus::Suspended->value));
  }

  #[Test]
  public function unknownStatusValueIsRejected(): void {
    $adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);

    self::assertSame(422, $this->changeStatus($adminToken, $memberId, 'not-a-real-status'));
  }
}
