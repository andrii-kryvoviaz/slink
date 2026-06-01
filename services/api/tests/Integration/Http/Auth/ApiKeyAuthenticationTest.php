<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Auth;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class ApiKeyAuthenticationTest extends HttpTestCase {
  /**
   * @return array{key: string, keyId: string}
   */
  private function createApiKey(string $token): array {
    $this->client->request(
      'POST',
      '/api/user/api-keys',
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => 'integration-key'], JSON_THROW_ON_ERROR),
    );

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), 'Create API key failed: ' . (string) $response->getContent());

    /** @var array{key: string, keyId: string} $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    self::assertArrayHasKey('key', $payload);
    self::assertArrayHasKey('keyId', $payload);
    self::assertStringStartsWith('sk_', $payload['key']);

    return $payload;
  }

  private function storedKeyId(string $token): string {
    $this->apiRequest('GET', '/api/user/api-keys', $token);

    /** @var array{data?: array<int, array{id?: string}>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $keyId = $payload['data'][0]['id'] ?? '';
    self::assertNotSame('', $keyId, 'No stored API key found.');

    return $keyId;
  }

  private function revokeApiKey(string $token, string $keyId): void {
    $this->apiRequest('DELETE', \sprintf('/api/user/api-keys/%s', $keyId), $token);
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

  #[Test]
  public function validApiKeyAuthenticatesExternalUpload(): void {
    $this->createUser('apikey-owner@local.test', 'apikeyowner', self::PASSWORD);
    $token = $this->login('apikeyowner', self::PASSWORD);

    $apiKey = $this->createApiKey($token);

    self::assertSame(201, $this->externalUpload($apiKey['key']));
  }

  #[Test]
  public function revokedApiKeyIsRejected(): void {
    $this->createUser('apikey-revoke@local.test', 'apikeyrevoke', self::PASSWORD);
    $token = $this->login('apikeyrevoke', self::PASSWORD);

    $apiKey = $this->createApiKey($token);
    self::assertSame(201, $this->externalUpload($apiKey['key']));

    self::assertSame(
      $this->storedKeyId($token),
      $apiKey['keyId'],
      'The keyId returned on create must equal the stored, revocable id.',
    );

    $this->revokeApiKey($token, $apiKey['keyId']);

    self::assertSame(401, $this->externalUpload($apiKey['key']));
  }

  #[Test]
  public function malformedApiKeyIsRejected(): void {
    self::assertSame(401, $this->externalUpload('sk_not_a_real_key'));
  }
}
