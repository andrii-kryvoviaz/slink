<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\ReadModel\View;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class UserViewTest extends TestCase {
  private function createUser(): UserView {
    return new UserView(
      ID::generate()->toString(),
      Email::fromString('member@example.com'),
      Username::fromString('member'),
      DisplayName::fromString('Member'),
      HashedPassword::encode('password123'),
      DateTime::now(),
      null,
      UserStatus::Active,
      null,
    );
  }

  #[Test]
  public function itNullsEmailAndUsername(): void {
    $user = $this->createUser();

    $user->revokeIdentity();

    self::assertNull($user->getEmail());
    self::assertNull($user->getUsername());
  }

  #[Test]
  public function itLeavesDisplayNameAndPasswordUntouched(): void {
    $user = $this->createUser();
    $password = $user->getPassword();

    $user->revokeIdentity();

    self::assertSame('Member', $user->getDisplayName());
    self::assertSame($password, $user->getPassword());
  }
}
