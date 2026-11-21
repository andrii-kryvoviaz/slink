<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use Doctrine\ORM\EntityManagerInterface;
use Slink\User\Application\Command\CreateUser\CreateUserCommand;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubjectSet;
use Tests\Integration\Http\Double\StubOAuthAdapter;

trait ReloadsUsers {
  private const array PREFERENCES = [
    'license.default' => 'cc-by',
    'navigation.landingPage' => 'upload',
    'display.theme' => 'nord',
  ];

  private function createUserWithRolesSubjectAndPreferences(
    string $email,
    string $username,
    string $displayName,
    string $subject,
  ): string {
    $userId = $this->createActiveUser($email, $username, $displayName);
    $this->grantAdmin($userId);
    $this->updatePreferences($this->login($username, self::PASSWORD));
    $this->createOAuthProvider('acme');
    $this->signInThroughSso($this->identity($subject, $email));

    return $userId;
  }

  private function createActiveUser(string $email, string $username, string $displayName): string {
    $command = new CreateUserCommand($email, self::PASSWORD, $username, $displayName, UserStatus::Active);
    $this->commandBus()->handle($command);

    return $command->getId()->toString();
  }

  private function updatePreferences(string $token): void {
    $status = $this->apiRequest(
      'PATCH',
      '/api/user/preferences',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(self::PREFERENCES, JSON_THROW_ON_ERROR),
    );

    self::assertContains($status, [200, 204], (string) $this->client->getResponse()->getContent());
  }

  private function identity(string $subject, string $email): OAuthIdentity {
    return new OAuthIdentity(
      OAuthSubject::fromPrimitives('acme', $subject),
      Email::fromString($email),
      DisplayName::fromString('Sso Name'),
      true,
    );
  }

  private function signInThroughSso(OAuthIdentity $identity): void {
    StubOAuthAdapter::setIdentity($identity);

    $status = $this->apiRequest(
      'POST',
      '/api/auth/sso/token',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['code' => 'auth-code-123', 'state' => 'state-123'], JSON_THROW_ON_ERROR),
    );

    self::assertSame(200, $status, 'SSO sign-in failed: ' . (string) $this->client->getResponse()->getContent());
  }

  private function changeUserStatus(string $adminToken, string $userId, UserStatus $status): int {
    return $this->apiRequest(
      'PATCH',
      '/api/user/status',
      $adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $userId, 'status' => $status->value], JSON_THROW_ON_ERROR),
    );
  }

  private function changePassword(string $token, string $newPassword): int {
    return $this->apiRequest(
      'POST',
      '/api/user/change-password',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode([
        'old_password' => self::PASSWORD,
        'password' => $newPassword,
        'confirm' => $newPassword,
      ], JSON_THROW_ON_ERROR),
    );
  }

  private function purgeUser(string $adminToken, string $userId): int {
    return $this->apiRequest('DELETE', '/api/user/' . $userId, $adminToken);
  }

  private function signOut(string $refreshToken): int {
    return $this->apiRequest(
      'POST',
      '/api/auth/logout',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['refresh_token' => $refreshToken], JSON_THROW_ON_ERROR),
    );
  }

  private function rotatedRefreshToken(string $refreshToken): string {
    self::assertSame(200, $this->refreshStatus($refreshToken), (string) $this->client->getResponse()->getContent());

    $rotated = $this->responsePayload()['refresh_token'] ?? null;
    self::assertIsString($rotated);

    return $rotated;
  }

  private function expireRefreshTokens(string $userId): void {
    $this->userEntityManager()->getConnection()->executeStatement(
      'UPDATE refresh_token SET expires_at = ? WHERE user_uuid = ?',
      ['2001-02-03 04:05:06', $userId],
    );
  }

  private function clearDisplayName(string $userId): void {
    $this->userEntityManager()->getConnection()->executeStatement(
      'UPDATE "user" SET display_name = NULL WHERE uuid = ?',
      [$userId],
    );
    $this->userEntityManager()->clear();
  }

  private function storedDisplayName(string $userId): mixed {
    return $this->userEntityManager()->getConnection()->fetchOne(
      'SELECT display_name FROM "user" WHERE uuid = ?',
      [$userId],
    );
  }

  private function userEntityManager(): EntityManagerInterface {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager;
  }

  private function userStore(): UserStoreRepositoryInterface {
    /** @var UserStoreRepositoryInterface $store */
    $store = static::getContainer()->get(UserStoreRepositoryInterface::class);

    return $store;
  }

  private function emailOf(User $user): ?string {
    /** @var Email|null $email */
    $email = new \ReflectionProperty(User::class, 'email')->getValue($user);

    return $email?->toString();
  }

  private function passwordHashOf(User $user): string {
    /** @var HashedPassword $hash */
    $hash = new \ReflectionProperty(User::class, 'hashedPassword')->getValue($user);

    return $hash->toString();
  }

  /**
   * @return list<string>
   */
  private function subjectsOf(User $user): array {
    /** @var OAuthSubjectSet $subjects */
    $subjects = new \ReflectionProperty(User::class, 'linkedOAuthSubjects')->getValue($user);

    return \array_values($subjects->toArray());
  }

  /**
   * @return list<string>
   */
  private function sortedRolesOf(User $user): array {
    $roles = \array_values($user->getRoles());
    \sort($roles);

    return $roles;
  }
}
