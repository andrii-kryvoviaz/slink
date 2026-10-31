<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Concurrency;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('sc-1203')]
final class ConcurrentSignInTest extends ConcurrentRequestTestCase {
  private const string USERNAME = 'concurrentmember';

  protected function setUp(): void {
    parent::setUp();

    $this->createUser('concurrent-member@local.test', self::USERNAME, self::PASSWORD);
  }

  /**
   * @return array{status: int, refreshToken: string}
   */
  private function signInFromAnotherKernel(): array {
    return $this->sessionFrom($this->requestFromAnotherKernel('POST', '/api/auth/login', null, [
      'username' => self::USERNAME,
      'password' => self::PASSWORD,
    ]));
  }

  /**
   * @return array{status: int, refreshToken: string}
   */
  private function refreshFromAnotherKernel(string $refreshToken): array {
    return $this->sessionFrom($this->requestFromAnotherKernel('POST', '/api/auth/refresh', null, [
      'refresh_token' => $refreshToken,
    ]));
  }

  /**
   * @param array{status: int, body: string} $response
   * @return array{status: int, refreshToken: string}
   */
  private function sessionFrom(array $response): array {
    self::assertSame(200, $response['status'], $response['body']);

    /** @var array{refresh_token: string} $payload */
    $payload = \json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);

    return ['status' => $response['status'], 'refreshToken' => $payload['refresh_token']];
  }

  private function assertSessionsStillRefresh(string ...$refreshTokens): void {
    foreach ($refreshTokens as $refreshToken) {
      self::assertSame(200, $this->refreshStatus($refreshToken));
    }
  }

  #[Test]
  public function overlappingSignInsOfOneUserAllSucceed(): void {
    $overlapping = null;

    $this->beforeTheNextFlush(function () use (&$overlapping): void {
      $overlapping = $this->signInFromAnotherKernel();
    });

    $session = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);

    self::assertIsArray($overlapping);
    $this->assertSessionsStillRefresh($session['refresh_token'], $overlapping['refreshToken']);
  }

  #[Test]
  public function overlappingTokenRefreshesOfOneUserAllSucceed(): void {
    $firstSession = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $secondSession = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $overlapping = null;

    $this->beforeTheNextFlush(function () use (&$overlapping, $secondSession): void {
      $overlapping = $this->refreshFromAnotherKernel($secondSession['refresh_token']);
    });

    self::assertSame(200, $this->refreshStatus($firstSession['refresh_token']));

    self::assertIsArray($overlapping);
    $this->assertSessionsStillRefresh($overlapping['refreshToken']);
  }

  #[Test]
  public function aSignInOverlappingATokenRefreshOfOneUserBothSucceed(): void {
    $session = $this->authenticateWithRefreshToken(self::USERNAME, self::PASSWORD);
    $overlapping = null;

    $this->beforeTheNextFlush(function () use (&$overlapping): void {
      $overlapping = $this->signInFromAnotherKernel();
    });

    self::assertSame(200, $this->refreshStatus($session['refresh_token']));

    self::assertIsArray($overlapping);
    $this->assertSessionsStillRefresh($overlapping['refreshToken']);
  }
}
