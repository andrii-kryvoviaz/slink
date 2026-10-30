<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Settings;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\User\Application\Command\DeleteOAuthProvider\DeleteOAuthProviderCommand;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1136')]
final class LocalAuthProviderRequirementTest extends HttpTestCase {
  private const string ENV_DEFAULT = 'USER_LOCAL_AUTH_ENABLED';
  private const string VIOLATION_PROPERTY = 'localAuthEnabled';
  private const string VIOLATION_MESSAGE = '/enabled SSO provider/i';

  /**
   * @return iterable<string, array{bool}>
   */
  public static function noEnabledProviderProvider(): iterable {
    yield 'no provider at all' => [false];
    yield 'only a disabled provider' => [true];
  }

  /**
   * @return iterable<string, array{string, bool}>
   */
  public static function unchangedByRefusalProvider(): iterable {
    yield 'env default on' => ['true', true];
    yield 'env default off' => ['false', false];
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function envDefaultProvider(): iterable {
    yield 'env default on' => ['true'];
    yield 'env default off' => ['false'];
  }

  /**
   * @return iterable<string, array{string, array<string, mixed>, string}>
   */
  public static function otherCategoryProvider(): iterable {
    yield 'access' => [
      'access',
      [
        'allowGuestUploads' => true,
        'allowUnauthenticatedAccess' => false,
        'requireSsl' => false,
        'requireAuthForMediaShares' => false,
        'requireAuthForCollectionShares' => false,
      ],
      'allowGuestUploads',
    ];
    yield 'image' => [
      'image',
      [
        'maxSize' => '7M',
        'stripExifMetadata' => true,
        'compressionQuality' => 80,
        'allowOnlyPublicImages' => true,
      ],
      'allowOnlyPublicImages',
    ];
  }

  #[Test]
  #[DataProvider('noEnabledProviderProvider')]
  public function itRefusesTurningLocalAuthOffWithoutAnEnabledProviderAndStoresNothing(bool $withDisabledProvider): void {
    $token = $this->bootAdmin();

    if ($withDisabledProvider) {
      $this->createOAuthProvider('disabled-sso', enabled: false);
    }

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['allowRegistration' => true])));

    $this->assertRefusedForMissingProvider(
      $this->saveUserSettings($token, $this->userPayload(['allowRegistration' => false, 'localAuthEnabled' => false])),
    );

    $user = $this->publicUserSettings();

    self::assertTrue($user['localAuthEnabled'] ?? null);
    self::assertTrue($user['allowRegistration'] ?? null);
  }

  #[Test]
  #[DataProvider('unchangedByRefusalProvider')]
  public function itAppliesTheProviderRequirementWhateverTheEnvDefaultIs(string $envDefault, bool $expected): void {
    $token = $this->bootAdmin();
    $this->useEnv([self::ENV_DEFAULT => $envDefault]);

    $this->assertRefusedForMissingProvider(
      $this->saveUserSettings($token, $this->userPayload(['localAuthEnabled' => false])),
    );

    self::assertSame($expected, $this->publicUserSettings()['localAuthEnabled'] ?? null);
  }

  #[Test]
  public function itRefusesKeepingLocalAuthOffOnceNoEnabledProviderIsLeft(): void {
    $token = $this->bootAdmin();
    $this->saveLocalAuthOffThenLoseTheProvider($token);

    $this->assertRefusedForMissingProvider(
      $this->saveUserSettings($token, $this->userPayload(['allowRegistration' => false, 'localAuthEnabled' => false])),
    );

    self::assertTrue($this->publicUserSettings()['allowRegistration'] ?? null);
  }

  #[Test]
  #[DataProvider('envDefaultProvider')]
  public function itTurnsLocalAuthOffWithAnEnabledProviderWhateverTheEnvDefaultIs(string $envDefault): void {
    $token = $this->bootAdmin();
    $this->useEnv([self::ENV_DEFAULT => $envDefault]);
    $this->createOAuthProvider('enabled-sso');

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['localAuthEnabled' => false])));

    self::assertFalse($this->publicUserSettings()['localAuthEnabled'] ?? null);
  }

  #[Test]
  public function itSavesOtherUserSettingsWithLocalAuthOnWhenNoProviderIsEnabled(): void {
    $token = $this->bootAdmin();

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['allowRegistration' => false, 'localAuthEnabled' => true])));

    $user = $this->publicUserSettings();

    self::assertTrue($user['localAuthEnabled'] ?? null);
    self::assertFalse($user['allowRegistration'] ?? null);
  }

  #[Test]
  public function itKeepsLocalAuthOffWhenTheSaveOmitsItWhileAnEnabledProviderExists(): void {
    $token = $this->bootAdmin();
    $this->createOAuthProvider('enabled-sso');

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['localAuthEnabled' => false])));
    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['allowRegistration' => false])));

    $user = $this->publicUserSettings();

    self::assertFalse($user['localAuthEnabled'] ?? null);
    self::assertFalse($user['allowRegistration'] ?? null);
  }

  #[Test]
  public function itRefusesASaveOmittingLocalAuthEnabledOnceNoEnabledProviderIsLeft(): void {
    $token = $this->bootAdmin();
    $this->saveLocalAuthOffThenLoseTheProvider($token);

    $this->assertRefusedForMissingProvider(
      $this->saveUserSettings($token, $this->userPayload(['allowRegistration' => false])),
    );

    $user = $this->publicUserSettings();

    self::assertFalse($user['localAuthEnabled'] ?? null);
    self::assertTrue($user['allowRegistration'] ?? null);
  }

  /**
   * @param array<string, mixed> $settings
   */
  #[Test]
  #[DataProvider('otherCategoryProvider')]
  public function itSavesOtherCategoriesWithoutAnEnabledProvider(string $category, array $settings, string $changed): void {
    $token = $this->bootAdmin();

    self::assertSame(204, $this->save($token, $category, $settings));

    self::assertTrue($this->adminSettings($token, $category)[$changed] ?? null);
  }

  /**
   * @param array<string, mixed> $settings
   */
  #[Test]
  #[DataProvider('otherCategoryProvider')]
  public function itSavesOtherCategoriesWithAnEnabledProvider(string $category, array $settings, string $changed): void {
    $token = $this->bootAdmin();
    $this->createOAuthProvider('enabled-sso');

    self::assertSame(204, $this->save($token, $category, $settings));

    self::assertTrue($this->adminSettings($token, $category)[$changed] ?? null);
  }

  /**
   * @param array<string, mixed> $settings
   */
  #[Test]
  #[DataProvider('otherCategoryProvider')]
  public function itSavesOtherCategoriesWhileLocalAuthIsOffAndNoEnabledProviderIsLeft(string $category, array $settings, string $changed): void {
    $token = $this->bootAdmin();
    $this->saveLocalAuthOffThenLoseTheProvider($token);

    self::assertSame(204, $this->save($token, $category, $settings));

    self::assertTrue($this->adminSettings($token, $category)[$changed] ?? null);
  }

  private function saveLocalAuthOffThenLoseTheProvider(string $token): void {
    $providerId = $this->createOAuthProvider('short-lived-sso');

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['localAuthEnabled' => false])));

    $this->commandBus()->handleSync(new DeleteOAuthProviderCommand($providerId));
  }

  private function assertRefusedForMissingProvider(int $status): void {
    self::assertSame(400, $status, (string) $this->client->getResponse()->getContent());

    $payload = $this->responsePayload();
    $violations = $payload['error']['violations'] ?? [];

    self::assertCount(1, $violations);
    self::assertSame(self::VIOLATION_PROPERTY, $violations[0]['property'] ?? null);
    self::assertMatchesRegularExpression(self::VIOLATION_MESSAGE, $violations[0]['message'] ?? '');
  }

  /**
   * @param array<string, mixed> $overrides
   *
   * @return array<string, mixed>
   */
  private function userPayload(array $overrides = []): array {
    return [
      'approvalRequired' => false,
      'allowRegistration' => true,
      'password' => [
        'minLength' => 8,
        'requirements' => 0,
      ],
      ...$overrides,
    ];
  }

  /**
   * @param array<string, mixed> $settings
   */
  private function saveUserSettings(string $token, array $settings): int {
    return $this->save($token, 'user', $settings);
  }

  /**
   * @param array<string, mixed> $settings
   */
  private function save(string $token, string $category, array $settings): int {
    return $this->apiRequest(
      'POST',
      '/api/settings',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['category' => $category, 'settings' => $settings], JSON_THROW_ON_ERROR),
    );
  }

  /**
   * @return array<string, mixed>
   */
  private function publicUserSettings(): array {
    $this->client->request('GET', '/api/settings/public');

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());

    $payload = $this->responsePayload();
    $user = $payload['data']['user'] ?? $payload['user'] ?? null;
    self::assertIsArray($user);

    return $user;
  }

  /**
   * @return array<string, mixed>
   */
  private function adminSettings(string $token, string $category): array {
    self::assertSame(200, $this->apiRequest('GET', '/api/settings/global', $token), (string) $this->client->getResponse()->getContent());

    $payload = $this->responsePayload();
    $settings = $payload['data'][$category] ?? $payload[$category] ?? null;
    self::assertIsArray($settings);

    return $settings;
  }
}
