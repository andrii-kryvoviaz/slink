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

  private ?string $originalEnvDefault = null;

  protected function setUp(): void {
    parent::setUp();

    $this->originalEnvDefault = $_ENV[self::ENV_DEFAULT] ?? null;
  }

  protected function tearDown(): void {
    $this->writeEnvDefault($this->originalEnvDefault);

    parent::tearDown();
  }

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

  #[Test]
  #[DataProvider('envDefaultProvider')]
  public function itResolvesPublicLocalAuthEnabledFromEnvDefaultWhenNothingIsSaved(string $envDefault, bool $expected): void {
    $this->useEnvDefault($envDefault);

    self::assertSame($expected, $this->publicLocalAuthEnabled());
  }

  #[Test]
  #[DataProvider('envDefaultProvider')]
  public function itIncludesLocalAuthEnabledInAdminSettingsBeforeAnySave(string $envDefault, bool $expected): void {
    $this->useEnvDefault($envDefault);
    $token = $this->bootAdmin();

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
    $this->useEnvDefault($envDefault);
    $token = $this->bootAdmin();

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['localAuthEnabled' => $saved])));

    self::assertSame($saved, $this->publicLocalAuthEnabled());
  }

  #[Test]
  public function itResetsSavedLocalAuthEnabledToTrueWhenOmittedFromSave(): void {
    $this->useEnvDefault('true');
    $token = $this->bootAdmin();

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(['localAuthEnabled' => false])));
    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload()));

    self::assertTrue($this->publicLocalAuthEnabled());
  }

  #[Test]
  public function itStoresTrueOverADisabledEnvDefaultWhenOmittedFromSave(): void {
    $this->useEnvDefault('false');
    $token = $this->bootAdmin();

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload()));

    self::assertTrue($this->publicLocalAuthEnabled());
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
    $this->writeEnvDefault($value);

    self::ensureKernelShutdown();
    $this->client = static::createClient();
    $this->client->disableReboot();
    $this->client->catchExceptions(true);
  }

  private function writeEnvDefault(?string $value): void {
    if ($value === null) {
      unset($_ENV[self::ENV_DEFAULT], $_SERVER[self::ENV_DEFAULT]);
      \putenv(self::ENV_DEFAULT);

      return;
    }

    $_ENV[self::ENV_DEFAULT] = $value;
    $_SERVER[self::ENV_DEFAULT] = $value;
    \putenv(self::ENV_DEFAULT . '=' . $value);
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
