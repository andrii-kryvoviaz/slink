<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Auth;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\User\Application\Command\CreateOAuthProvider\CreateOAuthProviderCommand;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Integration\Http\Double\StubOAuthAdapter;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1132')]
final class LocalAuthDisabledTest extends HttpTestCase {
  private const string NEW_PASSWORD = 'NewPassword456!';
  private const string PROVIDER_SLUG = 'acme';

  private const string USER_EMAIL = 'member@local.test';
  private const string USER_NAME = 'memberuser';
  private const string ADMIN_EMAIL = 'admin@local.test';
  private const string ADMIN_NAME = 'adminuser';

  protected function setUp(): void {
    parent::setUp();

    StubOAuthAdapter::reset();
    $this->createEnabledProvider();
  }

  protected function tearDown(): void {
    StubOAuthAdapter::reset();

    parent::tearDown();
  }

  /**
   * @return iterable<string, array{string, string}>
   */
  public static function rejectedCredentialsProvider(): iterable {
    yield 'admin with correct password' => [self::ADMIN_NAME, self::PASSWORD];
    yield 'admin signing in by email' => [self::ADMIN_EMAIL, self::PASSWORD];
    yield 'user with correct password' => [self::USER_NAME, self::PASSWORD];
    yield 'user with wrong password' => [self::USER_NAME, 'NotThePassword123!'];
    yield 'unknown username' => ['doesnotexist', self::PASSWORD];
    yield 'suspended user with correct password' => ['suspendeduser', self::PASSWORD];
  }

  /**
   * @return iterable<string, array{bool}>
   */
  public static function allowRegistrationProvider(): iterable {
    yield 'registration allowed' => [true];
    yield 'registration not allowed' => [false];
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function oldPasswordProvider(): iterable {
    yield 'correct old password' => [self::PASSWORD];
    yield 'wrong old password' => ['WrongOldPassword999!'];
  }

  #[Test]
  #[DataProvider('rejectedCredentialsProvider')]
  public function itRejectsLocalSignInBeforeLookingAtCredentials(string $username, string $password): void {
    $this->seedAccounts();
    $this->saveLocalAuthEnabled(false);

    $this->attemptSignIn($username, $password);

    $this->assertLocalAuthDisabledResponse();
    self::assertArrayNotHasKey('access_token', $this->responsePayload());
    self::assertArrayNotHasKey('refresh_token', $this->responsePayload());
  }

  #[Test]
  public function itAnswersSignInIdenticallyForValidInvalidAndUnknownCredentials(): void {
    $this->seedAccounts();
    $this->saveLocalAuthEnabled(false);

    $this->attemptSignIn(self::ADMIN_NAME, self::PASSWORD);
    $expected = $this->lastResponse();

    foreach (self::rejectedCredentialsProvider() as [$username, $password]) {
      $this->attemptSignIn($username, $password);

      self::assertSame($expected, $this->lastResponse());
    }
  }

  #[Test]
  public function itFailsEveryLocalEntryPointWithTheSameError(): void {
    $this->createUser(self::USER_EMAIL, self::USER_NAME, self::PASSWORD);
    $token = $this->login(self::USER_NAME, self::PASSWORD);
    $this->saveLocalAuthEnabled(false);

    $this->attemptSignIn(self::USER_NAME, self::PASSWORD);
    $this->assertLocalAuthDisabledResponse();
    $signIn = $this->lastResponse();

    $this->signUp('fresh@local.test', 'freshuser');
    self::assertSame($signIn, $this->lastResponse());

    $this->changePassword($token, self::PASSWORD);
    self::assertSame($signIn, $this->lastResponse());
  }

  #[Test]
  #[DataProvider('allowRegistrationProvider')]
  public function itRefusesSignupWithoutCreatingTheUser(bool $allowRegistration): void {
    $this->saveLocalAuthEnabled(false, $allowRegistration);

    $this->signUp('fresh@local.test', 'freshuser');

    $this->assertLocalAuthDisabledResponse();
    self::assertSame(0, $this->countRowsByColumn('user', 'email', 'fresh@local.test'));
    self::assertSame(0, $this->countRowsByColumn('user', 'username', 'freshuser'));
  }

  #[Test]
  #[DataProvider('oldPasswordProvider')]
  public function itRefusesChangePasswordAndKeepsTheOldPassword(string $oldPassword): void {
    $this->createUser(self::USER_EMAIL, self::USER_NAME, self::PASSWORD);
    $token = $this->login(self::USER_NAME, self::PASSWORD);
    $this->saveLocalAuthEnabled(false);

    $this->changePassword($token, $oldPassword);

    $this->assertLocalAuthDisabledResponse();

    $this->saveLocalAuthEnabled(true);

    self::assertSame(200, $this->attemptSignIn(self::USER_NAME, self::PASSWORD));
    self::assertNotSame(200, $this->attemptSignIn(self::USER_NAME, self::NEW_PASSWORD));
  }

  #[Test]
  public function itRefusesChangePasswordForAnAdmin(): void {
    $token = $this->bootAdmin();
    $this->saveLocalAuthEnabled(false);

    $this->changePassword($token, self::PASSWORD);

    $this->assertLocalAuthDisabledResponse();
  }

  #[Test]
  public function itStillRotatesRefreshTokens(): void {
    $this->createUser(self::USER_EMAIL, self::USER_NAME, self::PASSWORD);
    $tokens = $this->authenticateWithRefreshToken(self::USER_NAME, self::PASSWORD);
    $this->switchLocalAuthOff();

    self::assertSame(200, $this->refreshStatus($tokens['refresh_token']));

    $payload = $this->responsePayload();
    self::assertNotEmpty($payload['access_token'] ?? null);
    self::assertNotEmpty($payload['refresh_token'] ?? null);
    self::assertNotSame($tokens['refresh_token'], $payload['refresh_token']);
  }

  #[Test]
  public function itStillListsAndAuthorizesSsoProviders(): void {
    $this->switchLocalAuthOff();

    self::assertSame(200, $this->apiRequest('GET', '/api/auth/sso/providers'));
    self::assertContains(self::PROVIDER_SLUG, \array_column($this->responsePayload()['data'] ?? [], 'slug'));

    self::assertSame(200, $this->apiRequest(
      'POST',
      '/api/auth/sso/authorize',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['provider' => self::PROVIDER_SLUG], JSON_THROW_ON_ERROR),
    ));
    self::assertNotEmpty($this->responsePayload()['authorizationUrl'] ?? null);
  }

  #[Test]
  public function itStillSignsInAnExistingUserThroughSso(): void {
    $this->createUser(self::USER_EMAIL, self::USER_NAME, self::PASSWORD);
    $this->stubIdentity(self::USER_EMAIL);
    $this->switchLocalAuthOff();

    self::assertSame(200, $this->ssoToken());
    self::assertNotEmpty($this->responsePayload()['access_token'] ?? null);
    self::assertSame(1, $this->countRowsByColumn('user', 'email', self::USER_EMAIL));
  }

  #[Test]
  public function itStillCreatesANewUserThroughSso(): void {
    $this->stubIdentity('sso-new@local.test');
    $this->switchLocalAuthOff();

    self::assertSame(200, $this->ssoToken());
    self::assertNotEmpty($this->responsePayload()['access_token'] ?? null);
    self::assertSame(1, $this->countRowsByColumn('user', 'email', 'sso-new@local.test'));
  }

  #[Test]
  public function itStillAcceptsApiKeys(): void {
    $this->createUser(self::USER_EMAIL, self::USER_NAME, self::PASSWORD);
    $apiKey = $this->createApiKey($this->login(self::USER_NAME, self::PASSWORD));
    $this->switchLocalAuthOff();

    self::assertSame(201, $this->externalUpload($apiKey['key']));
  }

  #[Test]
  public function itStillCreatesUsersFromTheConsole(): void {
    $this->switchLocalAuthOff();

    $exitCode = $this->runConsoleCommand('user:create', [
      '--email' => 'console@local.test',
      '--username' => 'consoleuser',
      '--password' => self::PASSWORD,
      '--activate' => true,
    ]);

    self::assertSame(Command::SUCCESS, $exitCode);

    $this->saveLocalAuthEnabled(true);

    self::assertSame(200, $this->attemptSignIn('consoleuser', self::PASSWORD));
  }

  #[Test]
  public function itStillResetsPasswordsFromTheConsole(): void {
    $this->createUser(self::USER_EMAIL, self::USER_NAME, self::PASSWORD);
    $this->switchLocalAuthOff();

    $exitCode = $this->runConsoleCommand('user:reset-password', [
      '--email' => self::USER_EMAIL,
      '--password' => self::NEW_PASSWORD,
    ]);

    self::assertSame(Command::SUCCESS, $exitCode);

    $this->saveLocalAuthEnabled(true);

    self::assertSame(200, $this->attemptSignIn(self::USER_NAME, self::NEW_PASSWORD));
  }

  #[Test]
  public function itStillInitializesTheAdminFromTheConsole(): void {
    $this->useEnv([
      'ADMIN_USERNAME' => 'initadmin',
      'ADMIN_EMAIL' => 'init-admin@local.test',
      'ADMIN_PASSWORD' => self::PASSWORD,
    ]);
    $this->switchLocalAuthOff();

    self::assertSame(Command::SUCCESS, $this->runConsoleCommand('slink:admin:init'));

    $this->saveLocalAuthEnabled(true);

    self::assertSame(200, $this->attemptSignIn('initadmin', self::PASSWORD));
  }

  #[Test]
  public function itDisablesLocalAuthFromTheEnvDefaultWhenNothingIsSaved(): void {
    $this->useEnv(['USER_LOCAL_AUTH_ENABLED' => 'false']);

    $this->attemptSignIn('doesnotexist', self::PASSWORD);

    $this->assertLocalAuthDisabledResponse();
  }

  #[Test]
  public function itRestoresLocalSignInWhenSavedOnOverADisabledEnvDefault(): void {
    $this->useEnv(['USER_LOCAL_AUTH_ENABLED' => 'false']);
    $this->createUser(self::USER_EMAIL, self::USER_NAME, self::PASSWORD);

    $this->attemptSignIn(self::USER_NAME, self::PASSWORD);
    $this->assertLocalAuthDisabledResponse();

    $this->saveLocalAuthEnabled(true);

    self::assertSame(200, $this->attemptSignIn(self::USER_NAME, self::PASSWORD));
  }

  private function createEnabledProvider(): void {
    $this->commandBus()->handleSync(new CreateOAuthProviderCommand(
      name: 'Acme SSO',
      slug: self::PROVIDER_SLUG,
      clientId: 'client-id-123',
      discoveryUrl: 'https://sso.local.test/.well-known/openid-configuration',
      clientSecret: 'client-secret-456',
      enabled: true,
      registrationPolicy: 'allowed',
    ));
  }

  private function seedAccounts(): void {
    $this->grantAdmin($this->createUser(self::ADMIN_EMAIL, self::ADMIN_NAME, self::PASSWORD));
    $this->createUser(self::USER_EMAIL, self::USER_NAME, self::PASSWORD);
    $this->createUser('suspended@local.test', 'suspendeduser', self::PASSWORD, UserStatus::Suspended);
  }

  private function saveLocalAuthEnabled(bool $enabled, bool $allowRegistration = true): void {
    $this->saveSettings('user', [
      'approvalRequired' => false,
      'allowRegistration' => $allowRegistration,
      'localAuthEnabled' => $enabled,
      'password' => [
        'minLength' => 8,
        'requirements' => 0,
      ],
    ]);
  }

  private function switchLocalAuthOff(): void {
    $this->saveLocalAuthEnabled(false);

    $this->attemptSignIn('doesnotexist', self::PASSWORD);

    $this->assertLocalAuthDisabledResponse();
  }

  private function assertLocalAuthDisabledResponse(): void {
    $response = $this->client->getResponse();
    $status = $response->getStatusCode();

    self::assertGreaterThanOrEqual(400, $status, (string) $response->getContent());
    self::assertLessThan(500, $status, (string) $response->getContent());
    self::assertStringContainsStringIgnoringCase('local authentication', $this->errorMessage());
    self::assertStringContainsStringIgnoringCase('disabled', $this->errorMessage());
  }

  private function errorMessage(): string {
    $message = $this->responsePayload()['error']['message'] ?? '';

    return \is_string($message) ? $message : '';
  }

  /**
   * @phpstan-impure
   *
   * @return array{int, string}
   */
  private function lastResponse(): array {
    $response = $this->client->getResponse();

    return [$response->getStatusCode(), (string) $response->getContent()];
  }

  private function attemptSignIn(string $username, string $password): int {
    return $this->apiRequest(
      'POST',
      '/api/auth/login',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['username' => $username, 'password' => $password], JSON_THROW_ON_ERROR),
    );
  }

  private function changePassword(string $token, string $oldPassword): int {
    return $this->apiRequest(
      'POST',
      '/api/user/change-password',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode([
        'old_password' => $oldPassword,
        'password' => self::NEW_PASSWORD,
        'confirm' => self::NEW_PASSWORD,
      ], JSON_THROW_ON_ERROR),
    );
  }

  private function stubIdentity(string $email): void {
    StubOAuthAdapter::setIdentity(new OAuthIdentity(
      OAuthSubject::fromPrimitives(self::PROVIDER_SLUG, 'subject-' . \md5($email)),
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

  /**
   * @param array<string, bool|string> $input
   */
  private function runConsoleCommand(string $name, array $input = []): int {
    $application = new Application($this->client->getKernel());
    $tester = new CommandTester($application->find($name));

    return $tester->execute($input, ['interactive' => false]);
  }
}
