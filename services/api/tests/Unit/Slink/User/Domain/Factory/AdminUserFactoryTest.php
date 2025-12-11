<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Factory;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Factory\AdminUserFactory;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;

final class AdminUserFactoryTest extends TestCase {
  #[Test]
  public function itCreatesAdminUserWithActiveStatus(): void {
    $factory = $this->createFactory('admin', 'admin@test.com', 'password123');

    $user = $factory->createAdminUser();

    $this->assertEquals(UserStatus::Active, $user->getStatus());
  }

  #[Test]
  public function itCreatesAdminUserWithCorrectUsername(): void {
    $factory = $this->createFactory('myadmin', 'admin@test.com', 'password123');

    $user = $factory->createAdminUser();

    $this->assertEquals('myadmin', $user->getUsername()->toString());
  }

  #[Test]
  public function itReturnsValidConfigurationWhenEmailAndPasswordSet(): void {
    $factory = $this->createFactory('admin', 'admin@test.com', 'password123');

    $this->assertTrue($factory->hasValidConfiguration());
  }

  #[Test]
  public function itReturnsInvalidConfigurationWhenEmailEmpty(): void {
    $factory = $this->createFactory('admin', '', 'password123');

    $this->assertFalse($factory->hasValidConfiguration());
  }

  #[Test]
  public function itReturnsInvalidConfigurationWhenPasswordEmpty(): void {
    $factory = $this->createFactory('admin', 'admin@test.com', '');

    $this->assertFalse($factory->hasValidConfiguration());
  }

  #[Test]
  public function itReturnsMissingPasswordWhenEmailSetButPasswordEmpty(): void {
    $factory = $this->createFactory('admin', 'admin@test.com', '');

    $this->assertTrue($factory->isMissingPassword());
  }

  #[Test]
  public function itReturnsNotMissingPasswordWhenBothSet(): void {
    $factory = $this->createFactory('admin', 'admin@test.com', 'password123');

    $this->assertFalse($factory->isMissingPassword());
  }

  #[Test]
  public function itReturnsNotMissingPasswordWhenEmailEmpty(): void {
    $factory = $this->createFactory('admin', '', 'password123');

    $this->assertFalse($factory->isMissingPassword());
  }

  #[Test]
  public function itReturnsCorrectAdminUsername(): void {
    $factory = $this->createFactory('customadmin', 'admin@test.com', 'password123');

    $this->assertEquals('customadmin', $factory->getAdminUsername());
  }

  #[Test]
  public function itReturnsCorrectAdminEmail(): void {
    $factory = $this->createFactory('admin', 'custom@test.com', 'password123');

    $this->assertEquals('custom@test.com', $factory->getAdminEmail());
  }

  private function createFactory(string $username, string $email, string $password): AdminUserFactory {
    $uniqueEmailSpec = $this->createMock(UniqueEmailSpecificationInterface::class);
    $uniqueEmailSpec->method('isUnique')->willReturn(true);

    $uniqueUsernameSpec = $this->createMock(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $uniqueDisplayNameSpec = $this->createMock(UniqueDisplayNameSpecificationInterface::class);
    $uniqueDisplayNameSpec->method('isUnique')->willReturn(true);

    $userCreationContext = new UserCreationContext($uniqueEmailSpec, $uniqueUsernameSpec, $uniqueDisplayNameSpec);

    $userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $userRepository->method('getByUsername')->willReturn(null);

    return new AdminUserFactory($userCreationContext, $userRepository, $username, $email, $password);
  }
}
