<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Concurrency;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\User\Domain\Enum\UserRole;
use Slink\User\Domain\Enum\UserStatus;

#[Group('sc-1203')]
final class OptimisticConflictResponseTest extends ConcurrentRequestTestCase {
  private const string NEW_PASSWORD = 'NewPassword456!';
  private const string UNKNOWN_ID = '11111111-2222-4333-8444-555555555555';

  private string $adminToken = '';

  protected function setUp(): void {
    parent::setUp();

    $this->adminToken = $this->bootAdmin();
  }

  /**
   * @return iterable<string, array{UserStatus}>
   */
  public static function winningStatuses(): iterable {
    yield 'another status' => [UserStatus::Suspended];
    yield 'the same status' => [UserStatus::Banned];
  }

  /**
   * @return iterable<string, array{string, int}>
   */
  public static function rejectedWrites(): iterable {
    yield 'status change by a non-admin' => ['statusByNonAdmin', 403];
    yield 'status of an unknown user' => ['statusOfUnknownUser', 404];
    yield 'status that does not exist' => ['unknownStatus', 422];
    yield 'role grant by a non-admin' => ['grantByNonAdmin', 403];
    yield 'role grant to an unknown user' => ['grantToUnknownUser', 404];
    yield 'role grant without a role' => ['grantWithoutRole', 422];
    yield 'role revoke by a non-admin' => ['revokeByNonAdmin', 403];
    yield 'role revoke from an unknown user' => ['revokeFromUnknownUser', 404];
    yield 'role revoke without a role' => ['revokeWithoutRole', 422];
    yield 'password change with a mismatched confirmation' => ['mismatchedPassword', 422];
    yield 'image edit by a non-owner' => ['editByNonOwner', 403];
    yield 'edit of an unknown image' => ['editOfUnknownImage', 404];
    yield 'image edit with an invalid body' => ['editWithInvalidBody', 422];
  }

  private function changeStatus(string $userId, UserStatus $status): int {
    return $this->sendJson('PATCH', '/api/user/status', $this->adminToken, [
      'id' => $userId,
      'status' => $status->value,
    ]);
  }

  private function userColumn(string $userId, string $column): mixed {
    return $this->connection()->fetchOne(\sprintf('SELECT %s FROM "user" WHERE uuid = ?', $column), [$userId]);
  }

  private function hasRole(string $userId, UserRole $role): bool {
    return (bool) $this->connection()->fetchOne(
      'SELECT COUNT(*) FROM user_to_role WHERE user_id = ? AND role = ?',
      [$userId, $role->value],
    );
  }

  private function assertConflict(int $status): void {
    self::assertSame(409, $status, (string) $this->client->getResponse()->getContent());
    self::assertSame(['error' => ['title' => 'Conflict', 'message' => 'Conflict']], $this->responsePayload());
    self::assertStringNotContainsStringIgnoringCase('doctrine', (string) $this->client->getResponse()->getContent());
    self::assertStringNotContainsStringIgnoringCase('lock', (string) $this->client->getResponse()->getContent());
  }

  private function assertAttempt(int $expected, string $username, string $password): void {
    $this->client->request(
      'POST',
      '/api/auth/login',
      [],
      [],
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['username' => $username, 'password' => $password], JSON_THROW_ON_ERROR),
    );

    self::assertSame($expected, $this->client->getResponse()->getStatusCode());
  }

  private function winStatusFromAnotherKernel(string $userId, UserStatus $status): void {
    $change = $this->requestFromAnotherKernel('PATCH', '/api/user/status', $this->adminToken, [
      'id' => $userId,
      'status' => $status->value,
    ]);

    self::assertSame(200, $change['status'], $change['body']);
  }

  private function winEditFromAnotherKernel(string $imageId, string $token, bool $isPublic): void {
    $edit = $this->requestFromAnotherKernel('PATCH', '/api/image/' . $imageId, $token, [
      'isPublic' => $isPublic,
      'description' => 'winner caption',
    ]);

    self::assertContains($edit['status'], [200, 204], $edit['body']);
  }

  #[Test]
  #[DataProvider('winningStatuses')]
  public function aStatusChangeThatLostAnswersConflictAndKeepsTheWinnersStatus(UserStatus $winner): void {
    $targetId = $this->createUser('conflict-target@local.test', 'conflicttarget', self::PASSWORD);

    $this->beforeTheNextFlush(function () use ($targetId, $winner): void {
      $this->winStatusFromAnotherKernel($targetId, $winner);
    });

    $this->assertConflict($this->changeStatus($targetId, UserStatus::Banned));
    self::assertSame($winner->value, $this->userColumn($targetId, 'status'));

    self::assertSame(200, $this->changeStatus($targetId, UserStatus::Active));
    self::assertSame(UserStatus::Active->value, $this->userColumn($targetId, 'status'));
  }

  #[Test]
  public function theConflictStatusHasItsReasonPhrase(): void {
    /** @var array<int, string> $messages */
    $messages = static::getContainer()->getParameter('exception_code_to_message');

    self::assertSame('Conflict', $messages[409] ?? null);
  }

  #[Test]
  public function aRoleGrantThatLostAnswersConflictAndStoresNoRole(): void {
    $targetId = $this->createUser('conflict-target@local.test', 'conflicttarget', self::PASSWORD);
    $grant = ['id' => $targetId, 'role' => UserRole::Admin->value];

    $this->beforeTheNextFlush(function () use ($targetId): void {
      $this->winStatusFromAnotherKernel($targetId, UserStatus::Suspended);
    });

    $this->assertConflict($this->sendJson('POST', '/api/user/role', $this->adminToken, $grant));
    self::assertFalse($this->hasRole($targetId, UserRole::Admin));
    self::assertSame(UserStatus::Suspended->value, $this->userColumn($targetId, 'status'));

    self::assertSame(200, $this->sendJson('POST', '/api/user/role', $this->adminToken, $grant));
    self::assertTrue($this->hasRole($targetId, UserRole::Admin));
  }

  #[Test]
  public function aRoleRevokeThatLostAnswersConflictAndKeepsTheRole(): void {
    $targetId = $this->createUser('conflict-target@local.test', 'conflicttarget', self::PASSWORD);
    $this->grantAdmin($targetId);
    $revoke = ['id' => $targetId, 'role' => UserRole::Admin->value];

    $this->beforeTheNextFlush(function () use ($targetId): void {
      $this->winStatusFromAnotherKernel($targetId, UserStatus::Suspended);
    });

    $this->assertConflict($this->sendJson('DELETE', '/api/user/role', $this->adminToken, $revoke));
    self::assertTrue($this->hasRole($targetId, UserRole::Admin));
    self::assertSame(UserStatus::Suspended->value, $this->userColumn($targetId, 'status'));

    self::assertSame(200, $this->sendJson('DELETE', '/api/user/role', $this->adminToken, $revoke));
    self::assertFalse($this->hasRole($targetId, UserRole::Admin));
  }

  #[Test]
  public function aPasswordChangeThatLostAnswersConflictAndKeepsTheOldPassword(): void {
    $memberId = $this->createUser('conflict-member@local.test', 'conflictmember', self::PASSWORD);
    $token = $this->login('conflictmember', self::PASSWORD);
    $change = ['old_password' => self::PASSWORD, 'password' => self::NEW_PASSWORD, 'confirm' => self::NEW_PASSWORD];

    $this->beforeTheNextFlush(function () use ($token): void {
      $profile = $this->requestFromAnotherKernel('PATCH', '/api/user/profile', $token, ['display_name' => 'Winner Name']);
      self::assertContains($profile['status'], [200, 204], $profile['body']);
    });

    $this->assertConflict($this->sendJson('POST', '/api/user/change-password', $token, $change));
    self::assertSame('Winner Name', $this->userColumn($memberId, 'display_name'));
    $this->assertAttempt(200, 'conflictmember', self::PASSWORD);

    self::assertContains($this->sendJson('POST', '/api/user/change-password', $token, $change), [200, 204]);
    $this->assertAttempt(200, 'conflictmember', self::NEW_PASSWORD);
  }

  #[Test]
  public function anImageEditThatLostAnswersConflictAndRollsBackItsFeedMessage(): void {
    $this->createUser('conflict-owner@local.test', 'conflictowner', self::PASSWORD);
    $token = $this->login('conflictowner', self::PASSWORD);
    $imageId = $this->uploadImage($token, true);
    $this->consumeOne();

    $this->beforeTheNextFlush(function () use ($imageId, $token): void {
      $this->winEditFromAnotherKernel($imageId, $token, true);
    });

    $this->assertConflict($this->sendJson('PATCH', '/api/image/' . $imageId, $token, [
      'isPublic' => true,
      'description' => 'loser caption',
    ]));
    self::assertSame('winner caption', $this->imageColumn($imageId, 'description'));
    self::assertSame(1, $this->pending());

    self::assertContains($this->sendJson('PATCH', '/api/image/' . $imageId, $token, [
      'isPublic' => true,
      'description' => 'next caption',
    ]), [200, 204]);
    self::assertSame('next caption', $this->imageColumn($imageId, 'description'));
    self::assertSame(2, $this->pending());
  }

  #[Test]
  public function aBatchThatLostAConflictAnswersConflictAndChangesNoImage(): void {
    $this->createUser('conflict-owner@local.test', 'conflictowner', self::PASSWORD);
    $token = $this->login('conflictowner', self::PASSWORD);
    $contestedId = $this->uploadImage($token, false);
    $otherId = $this->uploadImage($token, false);
    $batch = ['imageIds' => [$contestedId, $otherId], 'isPublic' => true];

    $this->beforeTheNextFlush(function () use ($contestedId, $token): void {
      $this->winEditFromAnotherKernel($contestedId, $token, false);
    });

    $this->assertConflict($this->sendJson('PATCH', '/api/images/batch', $token, $batch));
    self::assertSame('winner caption', $this->imageColumn($contestedId, 'description'));
    self::assertFalse((bool) $this->imageColumn($contestedId, 'is_public'));
    self::assertFalse((bool) $this->imageColumn($otherId, 'is_public'));
    self::assertSame(0, $this->pending());

    self::assertSame(200, $this->sendJson('PATCH', '/api/images/batch', $token, $batch));
    self::assertTrue((bool) $this->imageColumn($contestedId, 'is_public'));
    self::assertTrue((bool) $this->imageColumn($otherId, 'is_public'));
  }

  #[Test]
  public function aConflictFreeBatchKeepsItsProcessedAndFailedLists(): void {
    $this->createUser('batch-owner@local.test', 'batchowner', self::PASSWORD);
    $token = $this->login('batchowner', self::PASSWORD);
    $imageId = $this->uploadImage($token, false);

    self::assertSame(200, $this->sendJson('PATCH', '/api/images/batch', $token, [
      'imageIds' => [$imageId, self::UNKNOWN_ID],
      'isPublic' => true,
    ]));

    /** @var array{processed: list<string>, failed: list<array{id: string}>} $result */
    $result = $this->responsePayload()['data'] ?? $this->responsePayload();
    self::assertSame([$imageId], $result['processed']);
    self::assertSame([self::UNKNOWN_ID], \array_column($result['failed'], 'id'));
  }

  #[Test]
  #[DataProvider('rejectedWrites')]
  public function aRejectedWriteKeepsItsAnswer(string $write, int $expected): void {
    $targetId = $this->createUser('rejected-target@local.test', 'rejectedtarget', self::PASSWORD);
    $this->createUser('rejected-member@local.test', 'rejectedmember', self::PASSWORD);
    $memberToken = $this->login('rejectedmember', self::PASSWORD);
    $targetToken = $this->login('rejectedtarget', self::PASSWORD);
    $imageId = $this->uploadImage($targetToken, true);
    $admin = UserRole::Admin->value;

    $status = match ($write) {
      'statusByNonAdmin' => $this->sendJson('PATCH', '/api/user/status', $memberToken, ['id' => $targetId, 'status' => 'banned']),
      'statusOfUnknownUser' => $this->sendJson('PATCH', '/api/user/status', $this->adminToken, ['id' => self::UNKNOWN_ID, 'status' => 'banned']),
      'unknownStatus' => $this->sendJson('PATCH', '/api/user/status', $this->adminToken, ['id' => $targetId, 'status' => 'not-a-status']),
      'grantByNonAdmin' => $this->sendJson('POST', '/api/user/role', $memberToken, ['id' => $targetId, 'role' => $admin]),
      'grantToUnknownUser' => $this->sendJson('POST', '/api/user/role', $this->adminToken, ['id' => self::UNKNOWN_ID, 'role' => $admin]),
      'grantWithoutRole' => $this->sendJson('POST', '/api/user/role', $this->adminToken, ['id' => $targetId, 'role' => '']),
      'revokeByNonAdmin' => $this->sendJson('DELETE', '/api/user/role', $memberToken, ['id' => $targetId, 'role' => $admin]),
      'revokeFromUnknownUser' => $this->sendJson('DELETE', '/api/user/role', $this->adminToken, ['id' => self::UNKNOWN_ID, 'role' => $admin]),
      'revokeWithoutRole' => $this->sendJson('DELETE', '/api/user/role', $this->adminToken, ['id' => $targetId, 'role' => '']),
      'mismatchedPassword' => $this->sendJson('POST', '/api/user/change-password', $memberToken, [
        'old_password' => self::PASSWORD,
        'password' => self::NEW_PASSWORD,
        'confirm' => 'Different789!',
      ]),
      'editByNonOwner' => $this->sendJson('PATCH', '/api/image/' . $imageId, $memberToken, ['isPublic' => false, 'description' => 'x']),
      'editOfUnknownImage' => $this->sendJson('PATCH', '/api/image/' . self::UNKNOWN_ID, $targetToken, ['isPublic' => false, 'description' => 'x']),
      'editWithInvalidBody' => $this->sendJson('PATCH', '/api/image/' . $imageId, $targetToken, ['description' => \str_repeat('x', 300)]),
      default => self::fail('Unknown write ' . $write),
    };

    self::assertSame($expected, $status, (string) $this->client->getResponse()->getContent());
  }
}
