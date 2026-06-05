<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Auth;

use PHPUnit\Framework\Attributes\Test;
use Slink\User\Domain\Enum\UserStatus;
use Tests\Integration\Http\HttpTestCase;

final class LoginFailureTest extends HttpTestCase {
  private function attemptLogin(string $username, string $password): int {
    $this->client->request(
      'POST',
      '/api/auth/login',
      [],
      [],
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['username' => $username, 'password' => $password], JSON_THROW_ON_ERROR),
    );

    return $this->client->getResponse()->getStatusCode();
  }

  #[Test]
  public function wrongPasswordForActiveUserIsRejected(): void {
    $this->createUser('wrong-password@local.test', 'wrongpassworduser', self::PASSWORD, UserStatus::Active);

    $status = $this->attemptLogin('wrongpassworduser', 'NotThePassword123!');

    self::assertNotSame(200, $status);
    self::assertSame(400, $status, 'Wrong password should yield InvalidCredentialsException.');
  }

  #[Test]
  public function nonExistentUsernameIsRejected(): void {
    $status = $this->attemptLogin('doesnotexist', self::PASSWORD);

    self::assertNotSame(200, $status);
    self::assertSame(400, $status, 'Unknown username should yield InvalidCredentialsException.');
  }

  #[Test]
  public function suspendedAccountIsRejected(): void {
    $this->createUser('suspended@local.test', 'suspendeduser', self::PASSWORD, UserStatus::Suspended);

    $status = $this->attemptLogin('suspendeduser', self::PASSWORD);

    self::assertNotSame(200, $status);
    self::assertSame(400, $status, 'Suspended account should yield InvalidCredentialsException.');
  }

  #[Test]
  public function inactiveAccountIsRejected(): void {
    $this->createUser('inactive@local.test', 'inactiveuser', self::PASSWORD, UserStatus::Inactive);

    $status = $this->attemptLogin('inactiveuser', self::PASSWORD);

    self::assertNotSame(200, $status);
    self::assertSame(400, $status, 'Inactive account should yield InvalidCredentialsException.');
  }
}
