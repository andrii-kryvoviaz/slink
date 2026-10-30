<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Auth;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\User\Application\Command\ChangePassword\ChangePasswordHandler;
use Slink\User\Application\Command\SignIn\SignInHandler;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1186')]
final class LocalAuthAccessRuleTest extends HttpTestCase {
  private const string REFUSAL_MESSAGE = 'Local authentication is disabled';
  private const string REFUSAL_TITLE = 'Symfony.Component.HttpKernel.Exception.HttpException';
  private const string REMOVED_GUARD = 'Slink\User\Application\Service\LocalAuthGuard';
  private const string REMOVED_EXCEPTION = 'Slink\User\Domain\Exception\LocalAuthDisabledException';

  private const string LOGIN = '/api/auth/login';
  private const string SIGNUP = '/api/auth/signup';
  private const string CHANGE_PASSWORD = '/api/user/change-password';

  private const string MEMBER_EMAIL = 'member@local.test';
  private const string MEMBER_NAME = 'memberuser';

  protected function setUp(): void {
    parent::setUp();

    $this->createOAuthProvider('acme');
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function anonymousEntryPointProvider(): iterable {
    yield 'sign-in' => [self::LOGIN];
    yield 'signup' => [self::SIGNUP];
  }

  /**
   * @return iterable<string, array{string, string}>
   */
  public static function signedInEntryPointProvider(): iterable {
    foreach (['member', 'admin'] as $caller) {
      yield $caller . ' on sign-in' => [$caller, self::LOGIN];
      yield $caller . ' on signup' => [$caller, self::SIGNUP];
      yield $caller . ' on change-password' => [$caller, self::CHANGE_PASSWORD];
    }
  }

  /**
   * @return iterable<string, array{string, string}>
   */
  public static function invalidBodyProvider(): iterable {
    yield 'sign-in with no fields' => [self::LOGIN, '{}'];
    yield 'sign-in with blank fields' => [self::LOGIN, '{"username":"","password":""}'];
    yield 'signup with no fields' => [self::SIGNUP, '{}'];
    yield 'signup with a mismatched confirmation' => [
      self::SIGNUP,
      '{"email":"fresh@local.test","username":"freshuser","password":"Password123!","confirm":"Different123!"}',
    ];
    yield 'change-password with no fields' => [self::CHANGE_PASSWORD, '{}'];
  }

  /**
   * @return iterable<string, array{string, string}>
   */
  public static function malformedBodyProvider(): iterable {
    yield from self::invalidBodyProvider();

    yield 'sign-in with unparseable JSON' => [self::LOGIN, '{not json'];
    yield 'signup with unparseable JSON' => [self::SIGNUP, '{not json'];
    yield 'change-password with unparseable JSON' => [self::CHANGE_PASSWORD, '{not json'];
  }

  /**
   * @return iterable<string, array{class-string}>
   */
  public static function formerlyGuardedHandlerProvider(): iterable {
    yield 'sign-in handler' => [SignInHandler::class];
    yield 'change-password handler' => [ChangePasswordHandler::class];
  }

  #[Test]
  #[DataProvider('anonymousEntryPointProvider')]
  public function itRefusesAnAnonymousCaller(string $path): void {
    $this->saveLocalAuthEnabled(false);

    $this->post($path, $this->validBody($path));

    $this->assertRefused();
  }

  #[Test]
  #[DataProvider('signedInEntryPointProvider')]
  public function itRefusesASignedInCallerWhateverTheirRole(string $caller, string $path): void {
    $token = $this->signIn($caller);
    $this->saveLocalAuthEnabled(false);

    $this->post($path, $this->validBody($path), $token);

    $this->assertRefused();
  }

  #[Test]
  #[DataProvider('malformedBodyProvider')]
  public function itKeepsTheValidationAnswerToAMalformedBodyWhenLocalAuthIsOff(string $path, string $body): void {
    $token = $this->signIn('member');
    $this->saveLocalAuthEnabled(true);
    $this->post($path, $body, $token);
    $withLocalAuthOn = $this->lastResponse();

    $this->saveLocalAuthEnabled(false);

    self::assertSame(422, $this->post($path, $body, $token));
    self::assertSame($withLocalAuthOn, $this->lastResponse());

    $this->post($path, $this->validBody($path), $token);
    $this->assertRefused();
  }

  #[Test]
  #[DataProvider('invalidBodyProvider')]
  public function itStillValidatesTheBodyWhenLocalAuthIsOn(string $path, string $body): void {
    $token = $this->signIn('member');
    $this->saveLocalAuthEnabled(true);

    self::assertSame(422, $this->post($path, $body, $token));
  }

  #[Test]
  public function itKeepsTheAnswerToAChangePasswordCallWithNoSignedInUser(): void {
    $this->saveLocalAuthEnabled(true);
    $this->post(self::CHANGE_PASSWORD, $this->validBody(self::CHANGE_PASSWORD));
    $withLocalAuthOn = $this->lastResponse();

    $this->saveLocalAuthEnabled(false);
    $this->post(self::CHANGE_PASSWORD, $this->validBody(self::CHANGE_PASSWORD));

    self::assertSame(401, $this->client->getResponse()->getStatusCode());
    self::assertSame($withLocalAuthOn, $this->lastResponse());
  }

  #[Test]
  public function itNoLongerShipsTheGuardOrItsException(): void {
    self::assertFalse(\class_exists(self::REMOVED_GUARD));
    self::assertFalse(\class_exists(self::REMOVED_EXCEPTION));
  }

  #[Test]
  public function itNoLongerMapsTheRemovedExceptionToAStatus(): void {
    /** @var array<string, int> $exceptionToStatus */
    $exceptionToStatus = static::getContainer()->getParameter('exception_to_status');

    self::assertArrayNotHasKey(self::REMOVED_EXCEPTION, $exceptionToStatus);
  }

  /**
   * @param class-string $handler
   */
  #[Test]
  #[DataProvider('formerlyGuardedHandlerProvider')]
  public function itLeavesHandlersFreeOfAnyLocalAuthCheck(string $handler): void {
    $dependencies = $this->constructorDependencies($handler);

    self::assertNotContains(self::REMOVED_GUARD, $dependencies);
    self::assertNotContains(ConfigurationProviderInterface::class, $dependencies);
  }

  private function saveLocalAuthEnabled(bool $enabled): void {
    $this->saveSettings('user', [
      'approvalRequired' => false,
      'allowRegistration' => true,
      'localAuthEnabled' => $enabled,
      'password' => [
        'minLength' => 8,
        'requirements' => 0,
      ],
    ]);
  }

  private function signIn(string $caller): string {
    if ($caller === 'admin') {
      return $this->bootAdmin();
    }

    $this->createUser(self::MEMBER_EMAIL, self::MEMBER_NAME, self::PASSWORD);

    return $this->login(self::MEMBER_NAME, self::PASSWORD);
  }

  private function validBody(string $path): string {
    $fields = match ($path) {
      self::LOGIN => ['username' => self::MEMBER_NAME, 'password' => self::PASSWORD],
      self::SIGNUP => [
        'email' => 'fresh@local.test',
        'username' => 'freshuser',
        'password' => self::PASSWORD,
        'confirm' => self::PASSWORD,
      ],
      default => [
        'old_password' => self::PASSWORD,
        'password' => 'NewPassword456!',
        'confirm' => 'NewPassword456!',
      ],
    };

    return \json_encode($fields, JSON_THROW_ON_ERROR);
  }

  private function post(string $path, string $body, ?string $token = null): int {
    return $this->apiRequest('POST', $path, $token, ['CONTENT_TYPE' => 'application/json'], $body);
  }

  private function assertRefused(): void {
    $response = $this->client->getResponse();
    $error = $this->responsePayload()['error'] ?? [];

    self::assertSame(403, $response->getStatusCode(), (string) $response->getContent());
    self::assertSame(self::REFUSAL_MESSAGE, $error['message'] ?? null);
    self::assertSame(self::REFUSAL_TITLE, $error['title'] ?? null);
  }

  /**
   * @return array{int, string}
   */
  private function lastResponse(): array {
    $response = $this->client->getResponse();

    return [$response->getStatusCode(), (string) $response->getContent()];
  }

  /**
   * @param class-string $class
   *
   * @return list<string>
   */
  private function constructorDependencies(string $class): array {
    $parameters = new \ReflectionClass($class)->getConstructor()?->getParameters() ?? [];

    return \array_map(
      static fn (\ReflectionParameter $parameter): string => (string) $parameter->getType(),
      $parameters,
    );
  }
}
