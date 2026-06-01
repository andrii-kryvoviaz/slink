<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Registration;

use PHPUnit\Framework\Attributes\Test;
use Slink\User\Domain\Enum\UserStatus;
use Tests\Integration\Http\HttpTestCase;

final class SignupRulesTest extends HttpTestCase {
  /**
   * @param array{approvalRequired?: bool, allowRegistration?: bool, minLength?: int, requirements?: int} $overrides
   */
  private function configureUserSettings(array $overrides = []): void {
    $this->saveSettings('user', [
      'approvalRequired' => $overrides['approvalRequired'] ?? false,
      'allowRegistration' => $overrides['allowRegistration'] ?? true,
      'password' => [
        'minLength' => $overrides['minLength'] ?? 8,
        'requirements' => $overrides['requirements'] ?? 0,
      ],
    ]);
  }

  private function signUp(string $email, string $username, string $password): int {
    $this->client->request(
      'POST',
      '/api/auth/signup',
      [],
      [],
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode([
        'email' => $email,
        'username' => $username,
        'password' => $password,
        'confirm' => $password,
      ], JSON_THROW_ON_ERROR),
    );

    return $this->client->getResponse()->getStatusCode();
  }

  private function responseBody(): string {
    return (string) $this->client->getResponse()->getContent();
  }

  #[Test]
  public function signupIsBlockedWhenRegistrationIsDisabled(): void {
    $this->configureUserSettings(['allowRegistration' => false]);

    $status = $this->signUp('blocked@local.test', 'blockeduser', 'Password123!');

    self::assertSame(400, $status);
    self::assertStringContainsStringIgnoringCase('registration', $this->responseBody());
  }

  #[Test]
  public function newlyRegisteredUserIsPendingWhenApprovalRequired(): void {
    $this->configureUserSettings(['approvalRequired' => true]);

    $status = $this->signUp('pending@local.test', 'pendinguser', 'Password123!');
    self::assertContains($status, [200, 201, 202, 204]);

    $loginStatus = $this->apiRequest(
      'POST',
      '/api/auth/login',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['username' => 'pendinguser', 'password' => 'Password123!'], JSON_THROW_ON_ERROR),
    );

    self::assertNotSame(200, $loginStatus);
    self::assertContains($loginStatus, [400, 401, 403]);
  }

  #[Test]
  public function approvedFlowCreatesAuthenticatableUser(): void {
    $this->configureUserSettings(['approvalRequired' => false]);

    $status = $this->signUp('approved@local.test', 'approveduser', 'Password123!');
    self::assertContains($status, [200, 201, 202, 204]);

    $token = $this->login('approveduser', 'Password123!');
    self::assertNotSame('', $token);
  }

  #[Test]
  public function signupRejectsPasswordShorterThanConfiguredMinimum(): void {
    $this->configureUserSettings(['minLength' => 12]);

    $status = $this->signUp('short@local.test', 'shortuser', 'Abc1!xy');

    self::assertContains($status, [400, 422]);
    self::assertStringContainsStringIgnoringCase('password', $this->responseBody());
  }

  #[Test]
  public function adminCreatedUserWithExplicitStatusBypassesRegistrationGate(): void {
    $this->configureUserSettings(['allowRegistration' => false]);

    $userId = $this->createUser('explicit@local.test', 'explicituser', self::PASSWORD, UserStatus::Active);
    self::assertNotSame('', $userId);

    $token = $this->login('explicituser', self::PASSWORD);
    self::assertNotSame('', $token);
  }
}
