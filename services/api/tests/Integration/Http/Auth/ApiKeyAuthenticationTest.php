<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Auth;

use PHPUnit\Framework\Attributes\Test;
use Slink\User\Domain\Enum\UserStatus;
use Tests\Integration\Http\HttpTestCase;

final class ApiKeyAuthenticationTest extends HttpTestCase {
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

  private function suspendUser(string $adminToken, string $userId): void {
    $status = $this->apiRequest(
      'PATCH',
      '/api/user/status',
      $adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $userId, 'status' => UserStatus::Suspended->value], JSON_THROW_ON_ERROR),
    );

    self::assertSame(200, $status, 'Suspending the user failed.');
  }

  #[Test]
  public function suspendedOwnerApiKeyIsRejected(): void {
    $ownerId = $this->createUser('apikey-suspended@local.test', 'apikeysuspended', self::PASSWORD);
    $ownerToken = $this->login('apikeysuspended', self::PASSWORD);

    $apiKey = $this->createApiKey($ownerToken);
    self::assertSame(201, $this->externalUpload($apiKey['key']));

    $adminId = $this->createUser('apikey-admin@local.test', 'apikeyadmin', self::PASSWORD);
    $this->grantAdmin($adminId);
    $adminToken = $this->login('apikeyadmin', self::PASSWORD);

    $this->suspendUser($adminToken, $ownerId);

    self::assertSame(401, $this->externalUpload($apiKey['key']));
  }

  #[Test]
  public function malformedApiKeyIsRejected(): void {
    self::assertSame(401, $this->externalUpload('sk_not_a_real_key'));
  }
}
