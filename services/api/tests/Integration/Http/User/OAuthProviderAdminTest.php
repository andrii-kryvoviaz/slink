<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class OAuthProviderAdminTest extends HttpTestCase {
  private function adminToken(): string {
    $adminId = $this->createUser('oauth-admin@local.test', 'oauthadmin', self::PASSWORD);
    $this->grantAdmin($adminId);

    return $this->login('oauthadmin', self::PASSWORD);
  }

  /**
   * @return array<string, mixed>
   */
  private function basePayload(): array {
    return [
      'name' => 'Acme SSO',
      'slug' => 'acme',
      'clientId' => 'client-id-123',
      'clientSecret' => 'client-secret-456',
      'discoveryUrl' => 'https://sso.local.test/.well-known/openid-configuration',
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  private function createProvider(string $token, array $payload): int {
    return $this->apiRequest(
      'POST',
      '/api/admin/oauth/providers',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode($payload, JSON_THROW_ON_ERROR),
    );
  }

  /**
   * @return array<string, mixed>
   */
  private function providerById(string $token, string $providerId): array {
    self::assertSame(200, $this->apiRequest('GET', '/api/admin/oauth/providers', $token));

    /** @var array<string, mixed> $payload */
    $payload = \json_decode(
      (string) $this->client->getResponse()->getContent(),
      true,
      512,
      JSON_THROW_ON_ERROR,
    );

    /** @var array<int, array<string, mixed>> $items */
    $items = $payload['data'] ?? $payload;

    foreach ($items as $item) {
      if (($item['id'] ?? null) === $providerId) {
        return $item;
      }
    }

    self::fail('Provider not found in admin payload: ' . $providerId);
  }

  #[Test]
  public function itRejectsUnknownRegistrationPolicyOnCreate(): void {
    $token = $this->adminToken();

    $status = $this->createProvider($token, [
      ...$this->basePayload(),
      'registrationPolicy' => 'banana',
    ]);

    self::assertSame(422, $status);
    self::assertStringContainsString(
      'registrationPolicy',
      (string) $this->client->getResponse()->getContent(),
    );
  }

  #[Test]
  public function itDefaultsPoliciesToInheritWhenCreateOmitsThem(): void {
    $token = $this->adminToken();

    $status = $this->createProvider($token, $this->basePayload());
    self::assertSame(201, $status);

    $providerId = $this->extractId((string) $this->client->getResponse()->getContent());
    $provider = $this->providerById($token, $providerId);

    self::assertSame('inherit', $provider['registrationPolicy'] ?? null);
    self::assertSame('inherit', $provider['approvalPolicy'] ?? null);
  }

  #[Test]
  public function itKeepsPoliciesWhenUpdateOmitsThem(): void {
    $token = $this->adminToken();

    $status = $this->createProvider($token, [
      ...$this->basePayload(),
      'registrationPolicy' => 'allowed',
      'approvalPolicy' => 'required',
    ]);
    self::assertSame(201, $status);

    $providerId = $this->extractId((string) $this->client->getResponse()->getContent());

    $status = $this->apiRequest(
      'PUT',
      \sprintf('/api/admin/oauth/providers/%s', $providerId),
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => 'Renamed SSO'], JSON_THROW_ON_ERROR),
    );
    self::assertSame(204, $status);

    $provider = $this->providerById($token, $providerId);

    self::assertSame('Renamed SSO', $provider['name'] ?? null);
    self::assertSame('allowed', $provider['registrationPolicy'] ?? null);
    self::assertSame('required', $provider['approvalPolicy'] ?? null);
  }
}
