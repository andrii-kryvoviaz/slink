<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Slink\User\Application\Command\CreateOAuthProvider\CreateOAuthProviderCommand;
use Slink\User\Application\Command\CreateUser\CreateUserCommand;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Tests\Integration\Http\Double\StubOAuthAdapter;
use Tests\Integration\Http\HttpTestCase;

final class UserSoftDeleteAnonymizationTest extends HttpTestCase {
  protected function setUp(): void {
    parent::setUp();

    StubOAuthAdapter::reset();
  }

  protected function tearDown(): void {
    parent::tearDown();

    StubOAuthAdapter::reset();
  }

  private function changeStatus(string $adminToken, string $userId, UserStatus $status): void {
    $result = $this->apiRequest(
      'PATCH',
      '/api/user/status',
      $adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $userId, 'status' => $status->value], JSON_THROW_ON_ERROR),
    );

    self::assertSame(200, $result, 'Status change failed: ' . (string) $this->client->getResponse()->getContent());
  }

  private function loginStatus(string $username, string $password): int {
    return $this->apiRequest(
      'POST',
      '/api/auth/login',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['username' => $username, 'password' => $password], JSON_THROW_ON_ERROR),
    );
  }

  private function externalUpload(string $apiKey): int {
    $this->client->request(
      'POST',
      '/api/external/upload',
      [],
      ['image' => $this->sampleImage()],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $apiKey],
    );

    return $this->client->getResponse()->getStatusCode();
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
  private function listExploreImageIds(): array {
    $status = $this->apiRequest('GET', '/api/images?limit=50');
    self::assertSame(200, $status, 'Image list failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data: array<int, array{id: string}>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return \array_map(static fn(array $row): string => $row['id'], $payload['data']);
  }

  private function assertDisplayNameAlreadyExistsResponse(): void {
    $payload = $this->responsePayload();

    self::assertSame(
      'Slink.User.Domain.Exception.DisplayNameAlreadyExistException',
      $payload['error']['title'] ?? null,
      (string) $this->client->getResponse()->getContent(),
    );
    self::assertSame(
      'display_name',
      $payload['error']['violations'][0]['property'] ?? null,
      (string) $this->client->getResponse()->getContent(),
    );
  }

  /**
   * @return array<string, array{0: UserStatus}>
   */
  public static function nonDeletedRestrictedStatusProvider(): array {
    return [
      'inactive' => [UserStatus::Inactive],
      'suspended' => [UserStatus::Suspended],
      'banned' => [UserStatus::Banned],
    ];
  }

  #[Test]
  public function softDeleteAnonymizesIdentityAndRevokesAccess(): void {
    $this->setAccessSettings([]);
    $adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    $adminImage = $this->uploadImage($adminToken, true);
    $this->postComment($memberToken, $adminImage, 'member comment on admin image');
    $apiKey = $this->createApiKey($memberToken);
    self::assertSame(201, $this->externalUpload($apiKey['key']));

    $this->softDelete($adminToken, $memberId);

    self::assertContains($this->loginStatus('memberuser', self::PASSWORD), [400, 401, 403]);
    self::assertSame(401, $this->externalUpload($apiKey['key']));

    $comments = $this->fetchComments($adminImage, $adminToken);
    self::assertCount(1, $comments);
    self::assertSame('member comment on admin image', $comments[0]['displayContent'] ?? null);
    self::assertArrayHasKey('author', $comments[0]);
    self::assertNull($comments[0]['author']);

    $this->allowRegistration();
    self::assertContains($this->signUp('member@local.test', 'memberuser'), [200, 201, 204]);
    $freshToken = $this->login('memberuser', self::PASSWORD);

    self::assertSame(200, $this->apiRequest('GET', '/api/user', $freshToken));
    $freshUser = $this->responsePayload();
    $freshUserId = $freshUser['data']['id'] ?? $freshUser['id'] ?? null;
    self::assertIsString($freshUserId);
    self::assertNotSame($memberId, $freshUserId);

    self::assertSame(200, $this->apiRequest('GET', '/api/user/api-keys', $freshToken));
    $apiKeys = $this->responsePayload();
    self::assertSame([], $apiKeys['data'] ?? null);
  }

  #[Test]
  public function reRegistrationWithTheHandleOfASoftDeletedUserSucceeds(): void {
    $this->setAccessSettings([]);
    $adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);

    $this->softDelete($adminToken, $memberId);

    $this->allowRegistration();
    self::assertContains($this->signUp('member@local.test', 'memberuser'), [200, 201, 204]);
  }

  #[Test]
  public function signUpIsRejectedWhenTheDisplayNameBelongsToAnActiveUser(): void {
    $this->createUserWithDisplayName('holder@local.test', 'holderuser', 'sharedname');

    $this->allowRegistration();
    self::assertSame(400, $this->signUp('other@local.test', 'sharedname'));
    $this->assertDisplayNameAlreadyExistsResponse();
  }

  #[Test]
  #[DataProvider('nonDeletedRestrictedStatusProvider')]
  public function signUpIsRejectedWhenTheDisplayNameBelongsToARestrictedUser(UserStatus $status): void {
    $adminToken = $this->bootAdmin();
    $holderId = $this->createUserWithDisplayName('holder@local.test', 'holderuser', 'sharedname');
    $this->changeStatus($adminToken, $holderId, $status);

    $this->allowRegistration();
    self::assertSame(400, $this->signUp('other@local.test', 'sharedname'));
    $this->assertDisplayNameAlreadyExistsResponse();
  }

  #[Test]
  public function profileUpdateIsRejectedWhenTheDisplayNameBelongsToAnActiveUser(): void {
    $this->createUserWithDisplayName('holder@local.test', 'holderuser', 'sharedname');
    $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    self::assertSame(400, $this->updateDisplayName($memberToken, 'sharedname'));
    $this->assertDisplayNameAlreadyExistsResponse();
  }

  #[Test]
  #[DataProvider('nonDeletedRestrictedStatusProvider')]
  public function profileUpdateIsRejectedWhenTheDisplayNameBelongsToARestrictedUser(UserStatus $status): void {
    $adminToken = $this->bootAdmin();
    $holderId = $this->createUserWithDisplayName('holder@local.test', 'holderuser', 'sharedname');
    $this->changeStatus($adminToken, $holderId, $status);

    $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    self::assertSame(400, $this->updateDisplayName($memberToken, 'sharedname'));
    $this->assertDisplayNameAlreadyExistsResponse();
  }

  #[Test]
  public function profileUpdateCanReuseTheDisplayNameOfASoftDeletedUser(): void {
    $adminToken = $this->bootAdmin();
    $holderId = $this->createUserWithDisplayName('holder@local.test', 'holderuser', 'sharedname');
    $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    $this->softDelete($adminToken, $holderId);

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
  public function ssoSignInWithProviderDisplayNameOfASoftDeletedUserSucceeds(): void {
    $this->allowRegistration();
    $adminToken = $this->bootAdmin();
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

    $this->softDelete($adminToken, $memberId);

    StubOAuthAdapter::setIdentity(new OAuthIdentity(
      OAuthSubject::fromPrimitives('acme', 'subject-returning-member'),
      Email::fromString('member.reborn@local.test'),
      DisplayName::fromString('memberuser'),
      true,
    ));

    $status = $this->apiRequest(
      'POST',
      '/api/auth/sso/token',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['code' => 'auth-code-123', 'state' => 'state-123'], JSON_THROW_ON_ERROR),
    );

    self::assertSame(200, $status, 'SSO sign-in failed: ' . (string) $this->client->getResponse()->getContent());
  }

  #[Test]
  public function ssoSignInWithEmailOfSoftDeletedUserCreatesFreshAccount(): void {
    $this->allowRegistration();
    $adminToken = $this->bootAdmin();
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

    $this->softDelete($adminToken, $memberId);

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
    self::assertNotSame($memberId, $freshUserId);
  }

  #[Test]
  public function softDeletePreservesOwnedContentAcrossImageCollectionAndTag(): void {
    $this->setAccessSettings([]);
    $adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    $imageShare = $this->createImageShare($memberToken, $memberImage);
    $this->publishShare($memberToken, $imageShare);
    $memberCollection = $this->createCollection($memberToken);
    $this->addImageToCollection($memberToken, $memberCollection, $memberImage);
    $this->createTag($memberToken, 'member-tag');

    $this->softDelete($adminToken, $memberId);

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s.png', $memberImage)));
    self::assertSame(1, $this->countRowsByColumn('image', 'user_id', $memberId));
    self::assertSame(1, $this->countRowsByColumn('collection', 'user_id', $memberId));
    self::assertSame(1, $this->countRowsByColumn('tag', 'user_id', $memberId));
  }

  #[Test]
  public function directLinksAndPublishedSharesSurviveSoftDeleteWhileBrowsingSurfacesHideThem(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);
    $adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    $imageShare = $this->createImageShare($memberToken, $memberImage);
    $this->publishShare($memberToken, $imageShare);

    $memberCollection = $this->createCollection($memberToken);
    $this->addImageToCollection($memberToken, $memberCollection, $memberImage);
    $collectionShare = $this->createCollectionShare($memberToken, $memberCollection);
    $this->publishShare($memberToken, $collectionShare);

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s.png', $memberImage)));
    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/collection/%s', $memberCollection)));
    self::assertContains($memberImage, $this->listExploreImageIds());

    $this->softDelete($adminToken, $memberId);

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s.png', $memberImage)));
    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/collection/%s', $memberCollection)));
    self::assertNotContains($memberImage, $this->listExploreImageIds());
  }

  #[Test]
  public function refreshTokenIssuedBeforeSoftDeleteIsRejectedAfterward(): void {
    $adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $tokens = $this->authenticateWithRefreshToken('memberuser', self::PASSWORD);

    $this->softDelete($adminToken, $memberId);

    self::assertSame(400, $this->refreshStatus($tokens['refresh_token']));
  }

  #[Test]
  public function ssoSignInReplayingAPreviouslyLinkedIdentityOfASoftDeletedUserCreatesFreshAccount(): void {
    $this->allowRegistration();
    $adminToken = $this->bootAdmin();

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

    $this->softDelete($adminToken, $originalUserId);

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
