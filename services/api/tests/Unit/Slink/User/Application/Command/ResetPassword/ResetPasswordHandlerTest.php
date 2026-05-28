<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\ResetPassword;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Application\Command\ResetPassword\ResetPasswordCommand;
use Slink\User\Application\Command\ResetPassword\ResetPasswordHandler;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Event\UserPasswordWasReset;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;

final class ResetPasswordHandlerTest extends TestCase {

  private const string VALID_EMAIL = 'test@example.com';
  private const string VALID_USERNAME = 'testuser';
  private const string VALID_PASSWORD = 'password123';
  private const string VALID_DISPLAY_NAME = 'Test User';
  private const string NEW_PASSWORD = 'newpassword123';

  #[Test]
  public function itResetsPasswordWhenUserExists(): void {
    $user = $this->createUser();

    $store = $this->createMock(UserStoreRepositoryInterface::class);
    $store->method('getByUsername')->willReturn($user);
    $store->expects($this->once())
      ->method('store')
      ->with($this->callback(function (User $storedUser) use ($user): bool {
        if ($storedUser !== $user) {
          return false;
        }

        $events = $storedUser->releaseEvents();

        return count($events) === 1
          && $events[0] instanceof UserPasswordWasReset
          && $events[0]->password->match(self::NEW_PASSWORD);
      }));

    $handler = new ResetPasswordHandler($store);

    $command = new ResetPasswordCommand(self::VALID_EMAIL, self::NEW_PASSWORD);

    $handler($command);
  }

  #[Test]
  public function itThrowsWhenUserNotFound(): void {
    $store = $this->createMock(UserStoreRepositoryInterface::class);
    $store->method('getByUsername')->willReturn(null);
    $store->expects($this->never())->method('store');

    $handler = new ResetPasswordHandler($store);

    $command = new ResetPasswordCommand(self::VALID_EMAIL, self::NEW_PASSWORD);

    $this->expectException(NotFoundException::class);

    $handler($command);
  }

  private function createUser(): User {
    $id = ID::generate();
    $credentials = Credentials::create(Email::fromString(self::VALID_EMAIL), Username::fromString(self::VALID_USERNAME), HashedPassword::encode(self::VALID_PASSWORD));
    $displayName = DisplayName::fromString(self::VALID_DISPLAY_NAME);
    $status = UserStatus::Active;
    $context = $this->createUserCreationContext();

    $user = User::create($id, $credentials, $displayName, $status, $context);
    $user->releaseEvents();

    return $user;
  }

  private function createUserCreationContext(): UserCreationContext {
    $uniqueEmailSpec = $this->createStub(UniqueEmailSpecificationInterface::class);
    $uniqueEmailSpec->method('isUnique')->willReturn(true);

    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $uniqueDisplayNameSpec = $this->createStub(UniqueDisplayNameSpecificationInterface::class);
    $uniqueDisplayNameSpec->method('isUnique')->willReturn(true);

    return new UserCreationContext($uniqueEmailSpec, $uniqueUsernameSpec, $uniqueDisplayNameSpec);
  }
}
