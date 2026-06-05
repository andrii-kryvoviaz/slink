<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Auth;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class RefreshTokenFlowTest extends HttpTestCase {
  /**
   * @return array{access_token: string, refresh_token: string}
   */
  private function authenticate(string $username, string $password): array {
    $this->client->request(
      'POST',
      '/api/auth/login',
      [],
      [],
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['username' => $username, 'password' => $password], JSON_THROW_ON_ERROR),
    );

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), 'Login failed: ' . (string) $response->getContent());

    /** @var array{access_token: string, refresh_token: string} $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    self::assertArrayHasKey('access_token', $payload);
    self::assertArrayHasKey('refresh_token', $payload);

    return $payload;
  }

  /**
   * @return array{0: int, 1: array<string, mixed>}
   */
  private function refresh(string $refreshToken): array {
    $this->client->request(
      'POST',
      '/api/auth/refresh',
      [],
      [],
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['refresh_token' => $refreshToken], JSON_THROW_ON_ERROR),
    );

    $response = $this->client->getResponse();

    /** @var array<string, mixed> $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR) ?: [];

    return [$response->getStatusCode(), $payload];
  }

  private function logout(string $refreshToken): int {
    $this->client->request(
      'POST',
      '/api/auth/logout',
      [],
      [],
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['refresh_token' => $refreshToken], JSON_THROW_ON_ERROR),
    );

    return $this->client->getResponse()->getStatusCode();
  }

  #[Test]
  public function validRefreshTokenReturnsNewTokenPair(): void {
    $this->createUser('refresh-rotate@local.test', 'refreshrotate', self::PASSWORD);
    $tokens = $this->authenticate('refreshrotate', self::PASSWORD);

    [$status, $payload] = $this->refresh($tokens['refresh_token']);

    self::assertSame(200, $status);
    self::assertArrayHasKey('access_token', $payload);
    self::assertArrayHasKey('refresh_token', $payload);
    self::assertIsString($payload['refresh_token']);
    self::assertNotSame($tokens['refresh_token'], $payload['refresh_token']);
  }

  #[Test]
  public function usedRefreshTokenIsRejectedAfterRotation(): void {
    $this->createUser('refresh-reuse@local.test', 'refreshreuse', self::PASSWORD);
    $tokens = $this->authenticate('refreshreuse', self::PASSWORD);

    [$firstStatus, $firstPayload] = $this->refresh($tokens['refresh_token']);
    self::assertSame(200, $firstStatus);
    self::assertIsString($firstPayload['refresh_token']);

    [$reuseStatus] = $this->refresh($tokens['refresh_token']);
    self::assertSame(400, $reuseStatus);

    [$rotatedStatus] = $this->refresh($firstPayload['refresh_token']);
    self::assertSame(200, $rotatedStatus);
  }

  #[Test]
  public function refreshTokenIsRejectedAfterLogout(): void {
    $this->createUser('refresh-logout@local.test', 'refreshlogout', self::PASSWORD);
    $tokens = $this->authenticate('refreshlogout', self::PASSWORD);

    self::assertContains($this->logout($tokens['refresh_token']), [200, 204]);

    [$status] = $this->refresh($tokens['refresh_token']);
    self::assertSame(400, $status);
  }
}
