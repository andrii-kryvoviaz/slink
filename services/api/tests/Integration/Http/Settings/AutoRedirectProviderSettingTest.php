<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Settings;

use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\Integration\Http\HttpTestCase;

final class AutoRedirectProviderSettingTest extends HttpTestCase {
  #[Test]
  public function itExposesNullAutoRedirectProviderIdOnFreshInstall(): void {
    $user = $this->requestPublicUserSettings();

    self::assertArrayHasKey('autoRedirectProviderId', $user);
    self::assertNull($user['autoRedirectProviderId']);
    self::assertArrayHasKey('allowRegistration', $user);
  }

  #[Test]
  public function itIncludesAutoRedirectProviderIdInAdminSettingsBeforeAnySave(): void {
    $token = $this->bootAdmin();

    $status = $this->apiRequest('GET', '/api/settings', $token);
    self::assertSame(200, $status, (string) $this->client->getResponse()->getContent());

    $payload = $this->responsePayload();
    $user = $payload['data']['user'] ?? $payload['user'] ?? null;

    self::assertIsArray($user);
    self::assertArrayHasKey('autoRedirectProviderId', $user);
    self::assertNull($user['autoRedirectProviderId']);
  }

  #[Test]
  public function itSavesAutoRedirectProviderIdThroughSettingsEndpoint(): void {
    $token = $this->bootAdmin();
    $providerId = Uuid::uuid4()->toString();

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload($providerId)));

    self::assertSame($providerId, $this->requestPublicUserSettings()['autoRedirectProviderId']);
  }

  #[Test]
  public function itClearsAutoRedirectProviderIdWhenSavedBlank(): void {
    $token = $this->bootAdmin();

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(Uuid::uuid4()->toString())));
    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload('')));

    self::assertNull($this->requestPublicUserSettings()['autoRedirectProviderId']);
  }

  #[Test]
  public function itClearsAutoRedirectProviderIdWhenSavedNull(): void {
    $token = $this->bootAdmin();

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(Uuid::uuid4()->toString())));
    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(null)));

    self::assertNull($this->requestPublicUserSettings()['autoRedirectProviderId']);
  }

  #[Test]
  public function itResetsAutoRedirectProviderIdWhenOmittedFromSave(): void {
    $token = $this->bootAdmin();

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload(Uuid::uuid4()->toString())));

    $payload = $this->userPayload(null);
    unset($payload['autoRedirectProviderId']);
    self::assertSame(204, $this->saveUserSettings($token, $payload));

    self::assertNull($this->requestPublicUserSettings()['autoRedirectProviderId']);
  }

  #[Test]
  public function itRejectsNonUuidAutoRedirectProviderIdAndKeepsStoredValue(): void {
    $token = $this->bootAdmin();
    $providerId = Uuid::uuid4()->toString();

    self::assertSame(204, $this->saveUserSettings($token, $this->userPayload($providerId)));
    $this->assertRejectedAsInvalidProvider($token);

    $user = $this->requestPublicUserSettings();
    self::assertSame($providerId, $user['autoRedirectProviderId']);
    self::assertTrue($user['allowRegistration']);
  }

  #[Test]
  public function itRejectsNonUuidOnFreshInstallAndKeepsNull(): void {
    $token = $this->bootAdmin();

    $this->assertRejectedAsInvalidProvider($token);

    self::assertNull($this->requestPublicUserSettings()['autoRedirectProviderId']);
  }

  private function assertRejectedAsInvalidProvider(string $token): void {
    $status = $this->saveUserSettings($token, $this->userPayload('not-a-uuid'));
    self::assertSame(400, $status, (string) $this->client->getResponse()->getContent());

    $payload = $this->responsePayload();
    self::assertSame('user.autoRedirectProviderId', $payload['error']['violations'][0]['property'] ?? null);
  }

  /**
   * @return array<string, mixed>
   */
  private function userPayload(?string $providerId): array {
    return [
      'approvalRequired' => false,
      'allowRegistration' => true,
      'password' => [
        'minLength' => 8,
        'requirements' => 0,
      ],
      'autoRedirectProviderId' => $providerId,
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

  /**
   * @return array<string, mixed>
   */
  private function requestPublicUserSettings(): array {
    $this->client->request('GET', '/api/settings/public');

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());

    /** @var array{data?: array{user?: array<string, mixed>}, user?: array<string, mixed>} $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $user = $payload['data']['user'] ?? $payload['user'] ?? null;
    self::assertIsArray($user);

    return $user;
  }
}
