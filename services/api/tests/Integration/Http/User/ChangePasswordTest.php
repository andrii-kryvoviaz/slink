<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class ChangePasswordTest extends HttpTestCase {
  private const string NEW_PASSWORD = 'NewPassword456!';

  private function changePassword(
    string $token,
    string $oldPassword,
    string $newPassword,
    ?string $confirm = null,
  ): int {
    return $this->apiRequest(
      'POST',
      '/api/user/change-password',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode([
        'old_password' => $oldPassword,
        'password' => $newPassword,
        'confirm' => $confirm ?? $newPassword,
      ], JSON_THROW_ON_ERROR),
    );
  }

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
  public function correctOldPasswordChangesPasswordAndRotatesCredentials(): void {
    $this->createUser('change-pass@local.test', 'changepass', self::PASSWORD);
    $token = $this->login('changepass', self::PASSWORD);

    self::assertContains(
      $this->changePassword($token, self::PASSWORD, self::NEW_PASSWORD),
      [200, 204],
      'Change password with correct old password should succeed.',
    );

    self::assertNotSame(
      200,
      $this->attemptLogin('changepass', self::PASSWORD),
      'Login with the old password must fail after a successful change.',
    );

    self::assertSame(
      200,
      $this->attemptLogin('changepass', self::NEW_PASSWORD),
      'Login with the new password must succeed after a successful change.',
    );
  }

  #[Test]
  public function wrongOldPasswordIsRejectedAndPasswordIsUnchanged(): void {
    $this->createUser('change-pass-wrong@local.test', 'changepasswrong', self::PASSWORD);
    $token = $this->login('changepasswrong', self::PASSWORD);

    self::assertContains(
      $this->changePassword($token, 'WrongOldPassword999!', self::NEW_PASSWORD),
      [400, 422],
      'Change password with a wrong old password must be rejected.',
    );

    self::assertSame(
      200,
      $this->attemptLogin('changepasswrong', self::PASSWORD),
      'Original password must still work after a rejected change.',
    );

    self::assertNotSame(
      200,
      $this->attemptLogin('changepasswrong', self::NEW_PASSWORD),
      'The attempted new password must not be usable after a rejected change.',
    );
  }
}
