<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Cache\CacheItemPoolInterface;
use Slink\User\Domain\Enum\UserStatus;
use Tests\Integration\Http\HttpTestCase;

final class TokenRevocationTest extends HttpTestCase {
  private function changeStatus(string $adminToken, string $userId, UserStatus $status): void {
    $responseStatus = $this->apiRequest(
      'PATCH',
      '/api/user/status',
      $adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $userId, 'status' => $status->value], JSON_THROW_ON_ERROR),
    );

    self::assertSame(
      200,
      $responseStatus,
      'Status change failed: ' . (string) $this->client->getResponse()->getContent(),
    );
  }

  private function purge(string $adminToken, string $userId): void {
    $status = $this->apiRequest('DELETE', '/api/user/' . $userId, $adminToken);

    self::assertSame(204, $status, 'Purge failed: ' . (string) $this->client->getResponse()->getContent());
  }

  private function forgeToken(string $userId, int $issuedAt): string {
    /** @var JWTEncoderInterface $encoder */
    $encoder = static::getContainer()->get('lexik_jwt_authentication.encoder');

    return $encoder->encode([
      'uuid' => $userId,
      'username' => 'memberuser',
      'roles' => ['ROLE_USER'],
      'iat' => $issuedAt,
      'exp' => $issuedAt + 3600,
    ]);
  }

  private function forgetPermissionsVersions(): void {
    /** @var CacheItemPoolInterface $pool */
    $pool = static::getContainer()->get('user_permissions_version');
    $pool->clear();
  }

  #[Test]
  public function tokenIssuedInTheSameSecondAsAPurgeIsRejected(): void {
    $adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    $this->purge($adminToken, $memberId);

    self::assertSame(401, $this->apiRequest('GET', '/api/user', $memberToken));
  }

  #[Test]
  public function tokenIssuedAfterAPurgeIsRejected(): void {
    $adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);

    $this->purge($adminToken, $memberId);

    self::assertSame(401, $this->apiRequest('GET', '/api/user', $this->forgeToken($memberId, \time())));
  }

  #[Test]
  public function tokenIssuedAfterASoftDeleteIsRejected(): void {
    $adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);

    $this->changeStatus($adminToken, $memberId, UserStatus::Deleted);

    self::assertSame(401, $this->apiRequest('GET', '/api/user', $this->forgeToken($memberId, \time())));
  }

  /**
   * @return iterable<string, array{UserStatus}>
   */
  public static function restrictedStatusProvider(): iterable {
    yield 'banned' => [UserStatus::Banned];
    yield 'suspended' => [UserStatus::Suspended];
    yield 'inactive' => [UserStatus::Inactive];
  }

  #[Test]
  #[DataProvider('restrictedStatusProvider')]
  public function tokenIssuedBeforeARestrictionIsRejected(UserStatus $status): void {
    $adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->forgeToken($memberId, \time() - 1);

    self::assertSame(200, $this->apiRequest('GET', '/api/user', $memberToken));

    $this->changeStatus($adminToken, $memberId, $status);

    self::assertSame(401, $this->apiRequest('GET', '/api/user', $memberToken));
  }

  #[Test]
  public function tokenIssuedAfterARestrictionIsLiftedIsAccepted(): void {
    $adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);

    $this->changeStatus($adminToken, $memberId, UserStatus::Banned);
    $this->changeStatus($adminToken, $memberId, UserStatus::Active);

    self::assertSame(200, $this->apiRequest('GET', '/api/user', $this->login('memberuser', self::PASSWORD)));
  }

  #[Test]
  public function deletedUserTokenIsRejectedAfterThePermissionsVersionCacheIsLost(): void {
    $adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    $this->changeStatus($adminToken, $memberId, UserStatus::Deleted);
    $this->forgetPermissionsVersions();

    self::assertSame(401, $this->apiRequest('GET', '/api/user', $memberToken));
  }

  #[Test]
  public function activeUserTokenSurvivesThePermissionsVersionCacheLoss(): void {
    $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    $this->forgetPermissionsVersions();

    self::assertSame(200, $this->apiRequest('GET', '/api/user', $memberToken));
  }

  #[Test]
  public function adminTokenSurvivesThePermissionsVersionCacheLoss(): void {
    $adminToken = $this->bootAdmin();

    $this->forgetPermissionsVersions();

    self::assertSame(200, $this->apiRequest('GET', '/api/users/1', $adminToken));
  }
}
