<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Application\Command\CreateApiKey\CreateApiKeyCommand;
use Slink\User\Application\Command\CreateOAuthProvider\CreateOAuthProviderCommand;
use Slink\User\Application\Command\CreateUser\CreateUserCommand;
use Slink\User\Application\Command\ResetPassword\ResetPasswordCommand;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Tests\Integration\Http\Double\StubOAuthAdapter;
use Tests\Integration\Http\HttpTestCase;

final class UserPurgeTest extends HttpTestCase {
  private string $adminToken = '';

  protected function setUp(): void {
    parent::setUp();

    StubOAuthAdapter::reset();
  }

  protected function tearDown(): void {
    parent::tearDown();

    StubOAuthAdapter::reset();
  }

  private function purge(?string $token, string $userId): int {
    return $this->apiRequest('DELETE', '/api/user/' . $userId, $token);
  }

  private function publishImageInOwnCollection(string $token, string $imageId): string {
    $collectionId = $this->createCollection($token);
    $this->addImageToCollection($token, $collectionId, $imageId);
    $shareId = $this->createCollectionShare($token, $collectionId);
    $this->publishShare($token, $shareId);

    return $collectionId;
  }

  private function createTag(string $token, string $name): void {
    $status = $this->apiRequest(
      'POST',
      '/api/tags',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => $name], JSON_THROW_ON_ERROR),
    );

    self::assertContains($status, [200, 201], 'Create tag failed: ' . (string) $this->client->getResponse()->getContent());
  }

  /**
   * @return array<int, string>
   */
  private function listBookmarkedImageIds(string $token): array {
    $status = $this->apiRequest('GET', '/api/bookmarks', $token);
    self::assertSame(200, $status, 'List bookmarks failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data?: array<int, array{image?: array{id?: string}}>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $ids = [];
    foreach ($payload['data'] ?? [] as $item) {
      $imageId = $item['image']['id'] ?? null;
      if (\is_string($imageId)) {
        $ids[] = $imageId;
      }
    }

    return $ids;
  }

  /**
   * @return array<int, string>
   */
  private function listUserIds(string $adminToken): array {
    $status = $this->apiRequest('GET', '/api/users/1', $adminToken);
    self::assertSame(200, $status, 'List users failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data?: array<int, array{id?: string}>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $ids = [];
    foreach ($payload['data'] ?? [] as $item) {
      if (isset($item['id'])) {
        $ids[] = $item['id'];
      }
    }

    return $ids;
  }

  /**
   * @return array{email: string|null, username: string|null}
   */
  private function fetchIdentityColumns(string $userId): array {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    /** @var array{email: string|null, username: string|null}|false $row */
    $row = $entityManager->getConnection()->fetchAssociative(
      'SELECT email, username FROM "user" WHERE uuid = :userId',
      ['userId' => $userId],
    );

    self::assertIsArray($row, 'Expected the purged user row to still exist.');

    return $row;
  }

  #[Test]
  public function anonymousCannotPurge(): void {
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);

    self::assertSame(401, $this->purge(null, $memberId));
  }

  #[Test]
  public function nonAdminCannotPurge(): void {
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);

    self::assertSame(403, $this->purge($nonOwnerToken, $memberId));
  }

  #[Test]
  public function adminPurgeOfUnknownUserReturnsNotFound(): void {
    $this->adminToken = $this->bootAdmin();

    self::assertSame(404, $this->purge($this->adminToken, '11111111-2222-3333-4444-555555555555'));
  }

  #[Test]
  public function adminCannotPurgeOwnAccount(): void {
    $adminId = $this->createUser('admin@local.test', 'adminuser', self::PASSWORD);
    $this->grantAdmin($adminId);
    $this->adminToken = $this->login('adminuser', self::PASSWORD);

    self::assertSame(400, $this->purge($this->adminToken, $adminId));
  }

  #[Test]
  public function nonUuidIdDoesNotMatchPurgeRoute(): void {
    $this->adminToken = $this->bootAdmin();

    self::assertSame(422, $this->purge($this->adminToken, 'role'));
  }

  #[Test]
  public function purgeCascadesAcrossOwnedContentAndIsIdempotent(): void {
    $this->setAccessSettings([]);
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);
    $nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    $imageShare = $this->createImageShare($memberToken, $memberImage);
    $this->publishShare($memberToken, $imageShare);

    $otherImage = $this->uploadImage($nonOwnerToken, true);

    $this->createTag($memberToken, 'member-tag');

    self::assertContains($this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $memberImage), $nonOwnerToken), [200, 201]);
    self::assertContains($this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $otherImage), $memberToken), [200, 201]);

    $this->postComment($memberToken, $otherImage, 'member comment on other image');
    $this->postComment($nonOwnerToken, $memberImage, 'nonowner comment on member image');

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s.png', $memberImage)));
    self::assertSame(1, $this->countRowsByColumn('bookmark', 'user_id', $memberId));
    self::assertSame(1, $this->countRowsByColumn('tag', 'user_id', $memberId));
    self::assertGreaterThan(0, $this->countRowsByColumn('notification', 'user_id', $memberId));

    $purgeStatus = $this->purge($this->adminToken, $memberId);
    self::assertSame(204, $purgeStatus, 'Purge failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertSame(404, $this->apiRequest('GET', \sprintf('/api/image/%s.png', $memberImage)));
    self::assertSame(0, $this->countRowsByColumn('bookmark', 'user_id', $memberId));
    self::assertSame(0, $this->countRowsByColumn('tag', 'user_id', $memberId));
    self::assertSame(0, $this->countRowsByColumn('notification', 'user_id', $memberId));
    self::assertNotContains($memberId, $this->listUserIds($this->adminToken));
    self::assertSame(401, $this->apiRequest('GET', '/api/user', $memberToken));

    $comments = $this->fetchComments($otherImage, $nonOwnerToken);
    self::assertCount(1, $comments);
    self::assertSame('member comment on other image', $comments[0]['displayContent'] ?? null);
    self::assertArrayHasKey('author', $comments[0]);
    self::assertNull($comments[0]['author']);

    $this->allowRegistration();
    self::assertContains($this->signUp('member@local.test', 'memberuser'), [200, 201, 204]);

    self::assertSame(204, $this->purge($this->adminToken, $memberId));
  }

  #[Test]
  public function purgeRemovesOtherUsersBookmarksOfPurgedImages(): void {
    $this->setAccessSettings([]);
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);
    $nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    self::assertContains($this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $memberImage), $nonOwnerToken), [200, 201]);
    self::assertContains($memberImage, $this->listBookmarkedImageIds($nonOwnerToken));

    self::assertSame(204, $this->purge($this->adminToken, $memberId));

    self::assertNotContains($memberImage, $this->listBookmarkedImageIds($nonOwnerToken));
  }

  #[Test]
  public function purgeDeletesOwnedCollections(): void {
    $this->setAccessSettings([]);
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    $memberCollection = $this->publishImageInOwnCollection($memberToken, $memberImage);

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/collection/%s', $memberCollection)));

    $purgeStatus = $this->purge($this->adminToken, $memberId);
    self::assertSame(204, $purgeStatus, 'Purge failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertSame(404, $this->apiRequest('GET', \sprintf('/api/collection/%s', $memberCollection)));
  }

  #[Test]
  public function purgeOfActiveUserAnonymizesCommentsAndFreesIdentity(): void {
    $this->setAccessSettings([]);
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    $adminImage = $this->uploadImage($this->adminToken, true);
    $this->publishImageInOwnCollection($this->adminToken, $adminImage);
    $this->postComment($memberToken, $adminImage, 'member comment on admin image');

    self::assertSame(204, $this->purge($this->adminToken, $memberId));

    $comments = $this->fetchComments($adminImage, $this->adminToken);
    self::assertCount(1, $comments);
    self::assertNull($comments[0]['author']);

    $this->allowRegistration();
    self::assertContains($this->signUp('member@local.test', 'memberuser'), [200, 201, 204]);
    $freshToken = $this->login('memberuser', self::PASSWORD);

    self::assertSame(200, $this->apiRequest('GET', '/api/user', $freshToken));
    $freshUser = $this->responsePayload();
    $freshUserId = $freshUser['data']['id'] ?? $freshUser['id'] ?? null;
    self::assertIsString($freshUserId);
    self::assertNotSame($memberId, $freshUserId);
  }

  #[Test]
  public function reRegistrationAfterPurgeWithTheSameHandleSucceeds(): void {
    $this->setAccessSettings([]);
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);

    self::assertSame(204, $this->purge($this->adminToken, $memberId));

    $this->allowRegistration();
    self::assertContains($this->signUp('member@local.test', 'memberuser'), [200, 201, 204]);
  }

  #[Test]
  public function profileUpdateCanReuseTheDisplayNameOfAPurgedUser(): void {
    $this->adminToken = $this->bootAdmin();
    $holderId = $this->createUserWithDisplayName('holder@local.test', 'holderuser', 'sharedname');
    $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    self::assertSame(204, $this->purge($this->adminToken, $holderId));

    self::assertContains(
      $this->updateDisplayName($memberToken, 'sharedname'),
      [200, 204],
      'Profile update failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    self::assertSame(200, $this->apiRequest('GET', '/api/user', $memberToken));
    $member = $this->responsePayload();
    self::assertSame('sharedname', $member['data']['displayName'] ?? null);
  }

  #[Test]
  public function purgingAlreadySoftDeletedUserRevokesCredentialsCreatedAfterSoftDelete(): void {
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);
    $this->createApiKey($memberToken);

    $statusChange = $this->apiRequest(
      'PATCH',
      '/api/user/status',
      $this->adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $memberId, 'status' => UserStatus::Deleted->value], JSON_THROW_ON_ERROR),
    );
    self::assertSame(200, $statusChange, 'Soft delete failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertSame(0, $this->countRowsByColumn('api_key', 'user_id', $memberId));
    self::assertSame(0, $this->countRowsByColumn('refresh_token', 'user_uuid', $memberId));

    $identity = $this->fetchIdentityColumns($memberId);
    self::assertNull($identity['email']);
    self::assertNull($identity['username']);

    $this->commandBus()->handleSync(
      (new CreateApiKeyCommand('key-created-after-soft-delete'))->withContext(['userId' => $memberId]),
    );
    self::assertSame(1, $this->countRowsByColumn('api_key', 'user_id', $memberId));

    self::assertSame(204, $this->purge($this->adminToken, $memberId));

    self::assertSame(0, $this->countRowsByColumn('api_key', 'user_id', $memberId));
    self::assertSame(0, $this->countRowsByColumn('refresh_token', 'user_uuid', $memberId));
    self::assertSame(0, $this->countRowsByColumn('oauth_link', 'user_id', $memberId));
  }

  #[Test]
  public function purgingAnAlreadyPurgedUserIsIdempotent(): void {
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);
    $this->createApiKey($memberToken);

    self::assertSame(204, $this->purge($this->adminToken, $memberId));
    self::assertSame(0, $this->countRowsByColumn('api_key', 'user_id', $memberId));
    self::assertSame(0, $this->countRowsByColumn('refresh_token', 'user_uuid', $memberId));

    self::assertSame(204, $this->purge($this->adminToken, $memberId));

    self::assertSame(0, $this->countRowsByColumn('api_key', 'user_id', $memberId));
    self::assertSame(0, $this->countRowsByColumn('refresh_token', 'user_uuid', $memberId));
    self::assertNotContains($memberId, $this->listUserIds($this->adminToken));
  }

  #[Test]
  public function independentlyDeletedUsersCoexistWithFreedIdentityAtPersistenceLevel(): void {
    $this->adminToken = $this->bootAdmin();
    $firstId = $this->createUser('first@local.test', 'firstuser', self::PASSWORD);
    $secondId = $this->createUser('second@local.test', 'seconduser', self::PASSWORD);

    foreach ([$firstId, $secondId] as $userId) {
      $status = $this->apiRequest(
        'PATCH',
        '/api/user/status',
        $this->adminToken,
        ['CONTENT_TYPE' => 'application/json'],
        \json_encode(['id' => $userId, 'status' => UserStatus::Deleted->value], JSON_THROW_ON_ERROR),
      );
      self::assertSame(200, $status, 'Soft delete failed: ' . (string) $this->client->getResponse()->getContent());
    }

    $firstIdentity = $this->fetchIdentityColumns($firstId);
    $secondIdentity = $this->fetchIdentityColumns($secondId);

    self::assertNull($firstIdentity['email']);
    self::assertNull($firstIdentity['username']);
    self::assertNull($secondIdentity['email']);
    self::assertNull($secondIdentity['username']);
  }

  #[Test]
  public function reRegistrationAfterPurgeStartsCompletelyFresh(): void {
    $this->setAccessSettings([]);
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    $adminImage = $this->uploadImage($this->adminToken, true);
    $memberImage = $this->uploadImage($memberToken, true);

    self::assertContains(
      $this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $adminImage), $memberToken),
      [200, 201],
    );
    $this->postComment($this->adminToken, $memberImage, 'admin comment on member image');
    $this->createApiKey($memberToken);
    $this->createCollection($memberToken);

    $preferencesUpdate = $this->apiRequest(
      'PATCH',
      '/api/user/preferences',
      $memberToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['image.defaultVisibility' => 'private'], JSON_THROW_ON_ERROR),
    );
    self::assertContains($preferencesUpdate, [200, 204], 'Update preferences failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertGreaterThan(0, $this->countRowsByColumn('bookmark', 'user_id', $memberId));
    self::assertGreaterThan(0, $this->countRowsByColumn('notification', 'user_id', $memberId));

    self::assertSame(204, $this->purge($this->adminToken, $memberId));

    $this->allowRegistration();
    self::assertContains($this->signUp('member@local.test', 'memberreborn'), [200, 201, 204]);
    $freshToken = $this->login('memberreborn', self::PASSWORD);

    self::assertSame(200, $this->apiRequest('GET', '/api/user', $freshToken));
    $freshUser = $this->responsePayload();
    $freshUserId = $freshUser['data']['id'] ?? null;
    self::assertIsString($freshUserId);
    self::assertNotSame($memberId, $freshUserId);

    self::assertSame([], $this->listBookmarkedImageIds($freshToken));

    self::assertSame(200, $this->apiRequest('GET', '/api/notifications/unread-count', $freshToken));
    $unread = $this->responsePayload();
    self::assertSame(0, $unread['count'] ?? null);

    self::assertSame(200, $this->apiRequest('GET', '/api/user/api-keys', $freshToken));
    $apiKeys = $this->responsePayload();
    self::assertSame([], $apiKeys['data'] ?? null);

    self::assertSame(200, $this->apiRequest('GET', '/api/collections', $freshToken));
    $collections = $this->responsePayload();
    self::assertSame([], $collections['data'] ?? null);

    self::assertSame(200, $this->apiRequest('GET', '/api/user/preferences', $freshToken));
    $preferences = $this->responsePayload();
    self::assertArrayNotHasKey('image.defaultVisibility', $preferences['data'] ?? []);
  }

  #[Test]
  public function ssoSignInWithEmailOfPurgedUserCreatesFreshAccount(): void {
    $this->setAccessSettings([]);
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);

    $this->commandBus()->handleSync(new CreateOAuthProviderCommand(
      name: 'Acme SSO',
      slug: 'acme',
      clientId: 'client-id-123',
      discoveryUrl: 'https://sso.local.test/.well-known/openid-configuration',
      clientSecret: 'client-secret-456',
      enabled: true,
      registrationPolicy: 'allowed',
      approvalPolicy: 'none',
    ));

    StubOAuthAdapter::setIdentity(new OAuthIdentity(
      OAuthSubject::fromPrimitives('acme', 'subject-member'),
      Email::fromString('member@local.test'),
      DisplayName::fromString('Sso Member'),
      true,
    ));

    self::assertSame(204, $this->purge($this->adminToken, $memberId));

    $status = $this->apiRequest(
      'POST',
      '/api/auth/sso/token',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['code' => 'auth-code-123', 'state' => 'state-123'], JSON_THROW_ON_ERROR),
    );

    self::assertSame(200, $status, 'SSO sign-in failed: ' . (string) $this->client->getResponse()->getContent());

    $payload = $this->responsePayload();
    $accessToken = $payload['accessToken'] ?? $payload['access_token'] ?? '';
    self::assertNotSame('', $accessToken);

    self::assertSame(200, $this->apiRequest('GET', '/api/user', (string) $accessToken));

    $user = $this->responsePayload();
    $freshUserId = $user['data']['id'] ?? null;

    self::assertIsString($freshUserId);
    self::assertNotSame($memberId, $freshUserId);
  }

  #[Test]
  public function authLookupsCannotFindPurgedUsersByTheirFormerIdentity(): void {
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);

    self::assertSame(204, $this->purge($this->adminToken, $memberId));

    $loginStatus = $this->apiRequest(
      'POST',
      '/api/auth/login',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['username' => 'memberuser', 'password' => self::PASSWORD], JSON_THROW_ON_ERROR),
    );
    self::assertContains($loginStatus, [400, 401, 403]);

    $this->expectException(NotFoundException::class);
    $this->commandBus()->handleSync(new ResetPasswordCommand('member@local.test', 'NewPassword456!'));
  }

  /**
   * @return array<string, array{0: UserStatus}>
   */
  public static function nonDeletedTargetStatusProvider(): array {
    return [
      'suspended' => [UserStatus::Suspended],
      'active' => [UserStatus::Active],
    ];
  }

  #[Test]
  #[DataProvider('nonDeletedTargetStatusProvider')]
  public function changingStatusOfAlreadyDeletedUserIsRejected(UserStatus $targetStatus): void {
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);

    $firstChange = $this->apiRequest(
      'PATCH',
      '/api/user/status',
      $this->adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $memberId, 'status' => UserStatus::Deleted->value], JSON_THROW_ON_ERROR),
    );
    self::assertSame(200, $firstChange, 'Soft delete failed: ' . (string) $this->client->getResponse()->getContent());

    $secondChange = $this->apiRequest(
      'PATCH',
      '/api/user/status',
      $this->adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $memberId, 'status' => $targetStatus->value], JSON_THROW_ON_ERROR),
    );
    self::assertSame(400, $secondChange, (string) $this->client->getResponse()->getContent());
  }

  #[Test]
  public function refreshTokenIssuedBeforePurgeIsRejectedAfterward(): void {
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $tokens = $this->authenticateWithRefreshToken('memberuser', self::PASSWORD);

    self::assertSame(204, $this->purge($this->adminToken, $memberId));

    self::assertSame(400, $this->refreshStatus($tokens['refresh_token']));
  }

  #[Test]
  public function ssoSignInReplayingAPreviouslyLinkedIdentityOfAPurgedUserCreatesFreshAccount(): void {
    $this->allowRegistration();
    $this->adminToken = $this->bootAdmin();

    $this->commandBus()->handleSync(new CreateOAuthProviderCommand(
      name: 'Acme SSO',
      slug: 'acme',
      clientId: 'client-id-123',
      discoveryUrl: 'https://sso.local.test/.well-known/openid-configuration',
      clientSecret: 'client-secret-456',
      enabled: true,
      registrationPolicy: 'allowed',
      approvalPolicy: 'none',
    ));

    StubOAuthAdapter::setIdentity(new OAuthIdentity(
      OAuthSubject::fromPrimitives('acme', 'subject-linked-member'),
      Email::fromString('linked-member@local.test'),
      DisplayName::fromString('Linked Member'),
      true,
    ));

    $registrationStatus = $this->apiRequest(
      'POST',
      '/api/auth/sso/token',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['code' => 'auth-code-123', 'state' => 'state-123'], JSON_THROW_ON_ERROR),
    );
    self::assertSame(200, $registrationStatus, 'SSO registration failed: ' . (string) $this->client->getResponse()->getContent());

    $registrationPayload = $this->responsePayload();
    $originalAccessToken = $registrationPayload['accessToken'] ?? $registrationPayload['access_token'] ?? '';
    self::assertNotSame('', $originalAccessToken);

    self::assertSame(200, $this->apiRequest('GET', '/api/user', (string) $originalAccessToken));
    $originalUser = $this->responsePayload();
    $originalUserId = $originalUser['data']['id'] ?? $originalUser['id'] ?? null;
    self::assertIsString($originalUserId);
    self::assertSame(1, $this->countRowsByColumn('oauth_link', 'user_id', $originalUserId));

    self::assertSame(204, $this->purge($this->adminToken, $originalUserId));

    $status = $this->apiRequest(
      'POST',
      '/api/auth/sso/token',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['code' => 'auth-code-123', 'state' => 'state-123'], JSON_THROW_ON_ERROR),
    );

    self::assertSame(200, $status, 'SSO sign-in failed: ' . (string) $this->client->getResponse()->getContent());

    $payload = $this->responsePayload();
    $accessToken = $payload['accessToken'] ?? $payload['access_token'] ?? '';
    self::assertNotSame('', $accessToken);

    self::assertSame(200, $this->apiRequest('GET', '/api/user', (string) $accessToken));

    $user = $this->responsePayload();
    $freshUserId = $user['data']['id'] ?? $user['id'] ?? null;

    self::assertIsString($freshUserId);
    self::assertNotSame($originalUserId, $freshUserId);
  }
}
