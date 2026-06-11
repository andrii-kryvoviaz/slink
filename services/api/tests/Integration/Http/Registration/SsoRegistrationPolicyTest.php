<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Registration;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Slink\User\Application\Command\CreateOAuthProvider\CreateOAuthProviderCommand;
use Slink\User\Application\Command\UpdateOAuthProvider\UpdateOAuthProviderCommand;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Tests\Integration\Http\Double\StubOAuthAdapter;
use Tests\Integration\Http\HttpTestCase;

final class SsoRegistrationPolicyTest extends HttpTestCase {
  protected function setUp(): void {
    parent::setUp();

    StubOAuthAdapter::reset();
  }

  protected function tearDown(): void {
    parent::tearDown();

    StubOAuthAdapter::reset();
  }

  private function configureUserSettings(bool $allowRegistration, bool $approvalRequired = false): void {
    $this->saveSettings('user', [
      'approvalRequired' => $approvalRequired,
      'allowRegistration' => $allowRegistration,
      'password' => [
        'minLength' => 8,
        'requirements' => 0,
      ],
    ]);
  }

  private function createProvider(
    string $slug,
    string $registrationPolicy = 'inherit',
    string $approvalPolicy = 'inherit',
  ): string {
    /** @var string $providerId */
    $providerId = $this->commandBus()->handleSync(new CreateOAuthProviderCommand(
      name: 'Acme SSO',
      slug: $slug,
      clientId: 'client-id-123',
      discoveryUrl: 'https://sso.local.test/.well-known/openid-configuration',
      clientSecret: 'client-secret-456',
      enabled: true,
      registrationPolicy: $registrationPolicy,
      approvalPolicy: $approvalPolicy,
    ));

    return $providerId;
  }

  private function updateRegistrationPolicy(string $providerId, string $registrationPolicy): void {
    $command = new UpdateOAuthProviderCommand(registrationPolicy: $registrationPolicy);

    $this->commandBus()->handleSync($command->withContext(['id' => $providerId]));
  }

  private function countUsersByEmail(string $email): int {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return (int) $entityManager->getConnection()->fetchOne(
      'SELECT COUNT(*) FROM "user" WHERE email = :email',
      ['email' => $email],
    );
  }

  private function stubIdentity(string $slug, string $email): void {
    StubOAuthAdapter::setIdentity(new OAuthIdentity(
      OAuthSubject::fromPrimitives($slug, 'subject-' . \md5($email)),
      Email::fromString($email),
      DisplayName::fromString('Sso User ' . \substr(\md5($email), 0, 6)),
      true,
    ));
  }

  private function ssoToken(): int {
    return $this->apiRequest(
      'POST',
      '/api/auth/sso/token',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['code' => 'auth-code-123', 'state' => 'state-123'], JSON_THROW_ON_ERROR),
    );
  }

  /**
   * @return array<string, mixed>
   */
  private function firstProvider(): array {
    $payload = $this->responsePayload();

    /** @var array<int, array<string, mixed>> $items */
    $items = $payload['data'] ?? $payload;
    self::assertNotEmpty($items);

    return $items[0];
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
  public function itIssuesTokenPairWhenProviderAllowsRegistrationAndGlobalRegistrationIsOff(): void {
    $this->configureUserSettings(allowRegistration: false);
    $this->createProvider('acme', registrationPolicy: 'allowed');
    $this->stubIdentity('acme', 'sso-allowed@local.test');

    $status = $this->ssoToken();

    self::assertSame(200, $status);

    $payload = $this->responsePayload();
    $accessToken = $payload['accessToken'] ?? $payload['access_token'] ?? '';

    self::assertNotSame('', $accessToken);
  }

  #[Test]
  public function itBlocksNewUserWhenProviderInheritsAndGlobalRegistrationIsOff(): void {
    $this->configureUserSettings(allowRegistration: false);
    $this->createProvider('acme');
    $this->stubIdentity('acme', 'sso-inherit@local.test');

    $status = $this->ssoToken();

    self::assertSame(400, $status);
    self::assertStringContainsStringIgnoringCase(
      'not allowed',
      (string) $this->client->getResponse()->getContent(),
    );
  }

  #[Test]
  public function itBlocksNewUserWhenProviderBlocksRegistrationAndGlobalRegistrationIsOn(): void {
    $this->configureUserSettings(allowRegistration: true);
    $this->createProvider('acme', registrationPolicy: 'blocked');
    $this->stubIdentity('acme', 'sso-blocked@local.test');

    $status = $this->ssoToken();

    self::assertSame(400, $status);
  }

  #[Test]
  public function itReturnsApprovalRequiredWhenProviderRequiresApproval(): void {
    $this->configureUserSettings(allowRegistration: false);
    $this->createProvider('acme', registrationPolicy: 'allowed', approvalPolicy: 'required');
    $this->stubIdentity('acme', 'sso-approval@local.test');

    $status = $this->ssoToken();

    self::assertSame(200, $status);

    $payload = $this->responsePayload();

    self::assertTrue($payload['approval_required'] ?? false);
  }

  #[Test]
  public function itExposesPoliciesInAdminPayloadOnly(): void {
    $this->configureUserSettings(allowRegistration: true);
    $this->createProvider('acme', registrationPolicy: 'allowed', approvalPolicy: 'required');

    $adminId = $this->createUser('admin@local.test', 'adminuser', self::PASSWORD);
    $this->grantAdmin($adminId);
    $token = $this->login('adminuser', self::PASSWORD);

    $status = $this->apiRequest('GET', '/api/admin/oauth/providers', $token);
    self::assertSame(200, $status);

    $adminProvider = $this->firstProvider();
    self::assertSame('allowed', $adminProvider['registrationPolicy'] ?? null);
    self::assertSame('required', $adminProvider['approvalPolicy'] ?? null);

    $status = $this->apiRequest('GET', '/api/auth/sso/providers');
    self::assertSame(200, $status);

    $publicProvider = $this->firstProvider();
    self::assertSame('acme', $publicProvider['slug'] ?? null);
    self::assertArrayNotHasKey('registrationPolicy', $publicProvider);
    self::assertArrayNotHasKey('approvalPolicy', $publicProvider);
  }

  #[Test]
  public function itKeepsIssuingTokensToExistingUserAfterProviderBlocksRegistration(): void {
    $this->configureUserSettings(allowRegistration: false);
    $providerId = $this->createProvider('acme', registrationPolicy: 'allowed');
    $this->stubIdentity('acme', 'sso-existing@local.test');

    self::assertSame(200, $this->ssoToken());

    $this->updateRegistrationPolicy($providerId, 'blocked');

    $status = $this->ssoToken();

    self::assertSame(200, $status);

    $payload = $this->responsePayload();
    $accessToken = $payload['accessToken'] ?? $payload['access_token'] ?? '';

    self::assertNotSame('', $accessToken);
  }

  #[Test]
  public function itKeepsReturningApprovalRequiredOnRepeatSignInWithoutDuplicatingUser(): void {
    $this->configureUserSettings(allowRegistration: false);
    $this->createProvider('acme', registrationPolicy: 'allowed', approvalPolicy: 'required');
    $this->stubIdentity('acme', 'sso-pending@local.test');

    self::assertSame(200, $this->ssoToken());
    self::assertTrue($this->responsePayload()['approval_required'] ?? false);

    self::assertSame(200, $this->ssoToken());
    self::assertTrue($this->responsePayload()['approval_required'] ?? false);

    self::assertSame(1, $this->countUsersByEmail('sso-pending@local.test'));
  }

  #[Test]
  public function itIssuesTokenPairWhenProviderWaivesApprovalAndGlobalApprovalIsOn(): void {
    $this->configureUserSettings(allowRegistration: true, approvalRequired: true);
    $this->createProvider('acme', approvalPolicy: 'none');
    $this->stubIdentity('acme', 'sso-no-approval@local.test');

    $status = $this->ssoToken();

    self::assertSame(200, $status);

    $payload = $this->responsePayload();
    $accessToken = $payload['accessToken'] ?? $payload['access_token'] ?? '';

    self::assertNotSame('', $accessToken);
  }
}
