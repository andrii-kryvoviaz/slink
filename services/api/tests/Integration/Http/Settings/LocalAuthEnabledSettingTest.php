<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Settings;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Repository\SettingsRepositoryInterface;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1131')]
final class LocalAuthEnabledSettingTest extends HttpTestCase {
  private const string ENV_DEFAULT = 'USER_LOCAL_AUTH_ENABLED';

  /**
   * @return iterable<string, array{string, bool}>
   */
  public static function envDefaultProvider(): iterable {
    yield 'enabled' => ['true', true];
    yield 'disabled' => ['false', false];
  }

  /**
   * @return iterable<string, array{string, bool}>
   */
  public static function savedOverEnvDefaultProvider(): iterable {
    yield 'saved false over env true' => ['true', false];
    yield 'saved true over env false' => ['false', true];
  }

  /**
   * @return iterable<string, array{string, bool, array<string, mixed>}>
   */
  public static function absentLocalAuthEnabledProvider(): iterable {
    yield 'saved false omitted, env true' => ['true', false, []];
    yield 'saved false null, env true' => ['true', false, ['localAuthEnabled' => null]];
    yield 'saved false omitted, env false' => ['false', false, []];
    yield 'saved true omitted, env false' => ['false', true, []];
    yield 'saved true null, env false' => ['false', true, ['localAuthEnabled' => null]];
  }

  /**
   * @return iterable<string, array{string, bool, array<string, mixed>}>
   */
  public static function absentLocalAuthEnabledWithoutSavedRowProvider(): iterable {
    yield 'env false omitted' => ['false', false, []];
    yield 'env false null' => ['false', false, ['localAuthEnabled' => null]];
    yield 'env true omitted' => ['true', true, []];
    yield 'env true null' => ['true', true, ['localAuthEnabled' => null]];
  }

  #[Test]
  #[DataProvider('envDefaultProvider')]
  public function itResolvesPublicLocalAuthEnabledFromEnvDefaultWhenNothingIsSaved(string $envDefault, bool $expected): void {
    $this->useEnvDefault($envDefault);

    self::assertSame($expected, $this->publicLocalAuthEnabled());
  }

  #[Test]
  #[DataProvider('envDefaultProvider')]
  public function itIncludesLocalAuthEnabledInAdminSettingsBeforeAnySave(string $envDefault, bool $expected): void {
    $token = $this->bootAdmin();
    $this->useEnvDefault($envDefault);

    $status = $this->apiRequest('GET', '/api/settings', $token);
    self::assertSame(200, $status, (string) $this->client->getResponse()->getContent());

    $payload = $this->responsePayload();
    $user = $payload['data']['user'] ?? $payload['user'] ?? null;

    self::assertIsArray($user);
    self::assertSame($expected, $user['localAuthEnabled'] ?? null);
  }

  #[Test]
  #[DataProvider('savedOverEnvDefaultProvider')]
  public function itPrefersTheAdminSavedValueOverTheEnvDefault(string $envDefault, bool $saved): void {
    $token = $this->bootAdmin();
    $this->useEnvDefault($envDefault);
    $this->createOAuthProvider('acme');

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['localAuthEnabled' => $saved])));

    self::assertSame($saved, $this->publicLocalAuthEnabled());
  }

  /**
   * @param array<string, mixed> $overrides
   */
  #[Test]
  #[DataProvider('absentLocalAuthEnabledProvider')]
  public function itKeepsTheSavedLocalAuthEnabledWhenTheSaveDoesNotCarryIt(string $envDefault, bool $saved, array $overrides): void {
    $token = $this->bootAdmin();
    $this->useEnvDefault($envDefault);
    $this->createOAuthProvider('acme');

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['localAuthEnabled' => $saved])));
    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload($overrides)));

    self::assertSame($saved, $this->publicLocalAuthEnabled());
  }

  /**
   * @param array<string, mixed> $overrides
   */
  #[Test]
  #[DataProvider('absentLocalAuthEnabledWithoutSavedRowProvider')]
  public function itKeepsTheEnvDefaultWhenNothingWasSavedAndTheSaveDoesNotCarryLocalAuthEnabled(string $envDefault, bool $expected, array $overrides): void {
    $token = $this->bootAdmin();
    $this->useEnvDefault($envDefault);
    $this->createOAuthProvider('acme');

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload($overrides)));

    self::assertSame($expected, $this->publicLocalAuthEnabled());
  }

  #[Test]
  public function itStillSavesAnExplicitLocalAuthEnabledOverASavedValue(): void {
    $token = $this->bootAdmin();
    $this->useEnvDefault('true');
    $this->createOAuthProvider('acme');

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['localAuthEnabled' => false])));
    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['localAuthEnabled' => true])));

    self::assertTrue($this->publicLocalAuthEnabled());
  }

  #[Test]
  public function itStillDefaultsOtherUserFlagsWhenTheyAreOmittedFromSave(): void {
    $token = $this->bootAdmin();
    $this->useEnvDefault('true');

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['allowRegistration' => false])));
    self::assertSame(204, $this->saveUserSettings($token, ['approvalRequired' => false, 'password' => ['minLength' => 8, 'requirements' => 0]]));

    $this->client->request('GET', '/api/settings/public');
    /** @var array{data?: array{user?: array<string, mixed>}, user?: array<string, mixed>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $user = $payload['data']['user'] ?? $payload['user'] ?? null;

    self::assertIsArray($user);
    self::assertTrue($user['allowRegistration'] ?? null);
  }

  #[Test]
  public function itFallsBackToTheEnvDefaultWhenSavedUserSettingsPredateTheFlag(): void {
    $this->useEnvDefault('false');
    $this->seedLegacyUserSettings();

    self::assertFalse($this->publicLocalAuthEnabled());
  }

  #[Test]
  public function itShipsLocalAuthEnabledByDefaultInTheExampleEnvironment(): void {
    $root = \dirname(__DIR__, 4);
    $path = \is_file($root . '/.env.example') ? $root . '/.env.example' : $root . '/.env';

    self::assertMatchesRegularExpression('/^USER_LOCAL_AUTH_ENABLED=true$/m', (string) \file_get_contents($path));
  }

  private function useEnvDefault(string $value): void {
    $this->useEnv([self::ENV_DEFAULT => $value]);
  }

  private function seedLegacyUserSettings(): void {
    /** @var SettingsRepositoryInterface $repository */
    $repository = static::getContainer()->get(SettingsRepositoryInterface::class);

    $repository->setBulk([
      'user.approvalRequired' => false,
      'user.allowRegistration' => true,
      'user.password.minLength' => 8,
      'user.password.requirements' => 0,
    ], SettingCategory::User);

    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $entityManager->flush();
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
    return $this->apiRequest(
      'POST',
      '/api/settings',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['category' => 'user', 'settings' => $settings], JSON_THROW_ON_ERROR),
    );
  }

  private function publicLocalAuthEnabled(): mixed {
    $this->client->request('GET', '/api/settings/public');

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());

    /** @var array{data?: array{user?: array<string, mixed>}, user?: array<string, mixed>} $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $user = $payload['data']['user'] ?? $payload['user'] ?? null;
    self::assertIsArray($user);

    return $user['localAuthEnabled'] ?? null;
  }
}
