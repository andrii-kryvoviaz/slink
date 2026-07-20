<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use PHPUnit\Framework\Attributes\Test;
use Slink\User\Application\Command\CreateOAuthProvider\CreateOAuthProviderCommand;
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

  private function bootAdmin(): string {
    $adminId = $this->createUser('admin@local.test', 'adminuser', self::PASSWORD);
    $this->grantAdmin($adminId);

    return $this->login('adminuser', self::PASSWORD);
  }

  private function softDelete(string $adminToken, string $userId): void {
    $status = $this->apiRequest(
      'PATCH',
      '/api/user/status',
      $adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $userId, 'status' => UserStatus::Deleted->value], JSON_THROW_ON_ERROR),
    );

    self::assertSame(200, $status, 'Soft delete failed: ' . (string) $this->client->getResponse()->getContent());
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

  private function postComment(string $token, string $imageId, string $content): void {
    $status = $this->apiRequest(
      'POST',
      \sprintf('/api/image/%s/comments', $imageId),
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['content' => $content], JSON_THROW_ON_ERROR),
    );

    self::assertSame(201, $status, 'Post comment failed: ' . (string) $this->client->getResponse()->getContent());
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function fetchComments(string $imageId, string $token): array {
    $status = $this->apiRequest('GET', \sprintf('/api/image/%s/comments', $imageId), $token);
    self::assertSame(200, $status, 'Fetch comments failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data: array<int, array<string, mixed>>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'];
  }

  private function allowRegistration(): void {
    $this->saveSettings('user', [
      'approvalRequired' => false,
      'allowRegistration' => true,
      'password' => [
        'minLength' => 8,
        'requirements' => 0,
      ],
    ]);
  }

  private function signUp(string $email, string $username): int {
    return $this->apiRequest(
      'POST',
      '/api/auth/signup',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode([
        'email' => $email,
        'username' => $username,
        'password' => self::PASSWORD,
        'confirm' => self::PASSWORD,
      ], JSON_THROW_ON_ERROR),
    );
  }

  /**
   * @return array<string, mixed>
   */
  private function responsePayload(): array {
    /** @var array<string, mixed> $payload */
    $payload = \json_decode(
      (string) $this->client->getResponse()->getContent(),
      true,
      512,
      JSON_THROW_ON_ERROR,
    ) ?: [];

    return $payload;
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
    self::assertSame(200, $this->loginStatus('memberuser', self::PASSWORD));
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
}
