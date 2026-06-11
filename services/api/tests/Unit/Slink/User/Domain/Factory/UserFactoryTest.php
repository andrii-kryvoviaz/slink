<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Factory;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Exception\RegistrationIsNotAllowed;
use Slink\User\Domain\Factory\UserFactory;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;

final class UserFactoryTest extends TestCase {

  #[Test]
  public function itCreatesActiveUserWhenRegistrationIsAllowed(): void {
    $factory = $this->createFactory(allowRegistration: true);

    $user = $factory->create(ID::generate(), $this->createCredentials(), $this->createDisplayName());

    $this->assertSame(UserStatus::Active, $user->getStatus());
  }

  #[Test]
  public function itThrowsWhenRegistrationIsOff(): void {
    $factory = $this->createFactory(allowRegistration: false);

    $this->expectException(RegistrationIsNotAllowed::class);

    $factory->create(ID::generate(), $this->createCredentials(), $this->createDisplayName());
  }

  #[Test]
  public function itCreatesInactiveUserWhenApprovalIsRequired(): void {
    $factory = $this->createFactory(allowRegistration: true, approvalRequired: true);

    $user = $factory->create(ID::generate(), $this->createCredentials(), $this->createDisplayName());

    $this->assertSame(UserStatus::Inactive, $user->getStatus());
  }

  #[Test]
  public function itCreatesUserWhenPolicyAllowsRegistrationAndGlobalRegistrationIsOff(): void {
    $factory = $this->createFactory(allowRegistration: false);

    $user = $factory->create(
      ID::generate(),
      $this->createCredentials(),
      $this->createDisplayName(),
      registrationPolicy: RegistrationPolicy::Allowed,
    );

    $this->assertSame(UserStatus::Active, $user->getStatus());
  }

  #[Test]
  public function itThrowsWhenPolicyInheritsAndGlobalRegistrationIsOff(): void {
    $factory = $this->createFactory(allowRegistration: false);

    $this->expectException(RegistrationIsNotAllowed::class);

    $factory->create(
      ID::generate(),
      $this->createCredentials(),
      $this->createDisplayName(),
      registrationPolicy: RegistrationPolicy::Inherit,
    );
  }

  #[Test]
  public function itThrowsWhenPolicyBlocksRegistrationAndGlobalRegistrationIsOn(): void {
    $factory = $this->createFactory(allowRegistration: true);

    $this->expectException(RegistrationIsNotAllowed::class);

    $factory->create(
      ID::generate(),
      $this->createCredentials(),
      $this->createDisplayName(),
      registrationPolicy: RegistrationPolicy::Blocked,
    );
  }

  #[Test]
  public function itCreatesInactiveUserWhenApprovalPolicyInheritsAndGlobalApprovalIsOn(): void {
    $factory = $this->createFactory(allowRegistration: false, approvalRequired: true);

    $user = $factory->create(
      ID::generate(),
      $this->createCredentials(),
      $this->createDisplayName(),
      registrationPolicy: RegistrationPolicy::Allowed,
      approvalPolicy: ApprovalPolicy::Inherit,
    );

    $this->assertSame(UserStatus::Inactive, $user->getStatus());
  }

  #[Test]
  public function itCreatesActiveUserWhenPolicyWaivesApprovalAndGlobalApprovalIsOn(): void {
    $factory = $this->createFactory(allowRegistration: false, approvalRequired: true);

    $user = $factory->create(
      ID::generate(),
      $this->createCredentials(),
      $this->createDisplayName(),
      registrationPolicy: RegistrationPolicy::Allowed,
      approvalPolicy: ApprovalPolicy::None,
    );

    $this->assertSame(UserStatus::Active, $user->getStatus());
  }

  #[Test]
  public function itCreatesInactiveUserWhenPolicyRequiresApprovalAndGlobalApprovalIsOff(): void {
    $factory = $this->createFactory(allowRegistration: true, approvalRequired: false);

    $user = $factory->create(
      ID::generate(),
      $this->createCredentials(),
      $this->createDisplayName(),
      approvalPolicy: ApprovalPolicy::Required,
    );

    $this->assertSame(UserStatus::Inactive, $user->getStatus());
  }

  #[Test]
  public function itBypassesPoliciesWhenStatusIsExplicit(): void {
    $factory = $this->createFactory(allowRegistration: false, approvalRequired: true);

    $user = $factory->create(
      ID::generate(),
      $this->createCredentials(),
      $this->createDisplayName(),
      UserStatus::Active,
      registrationPolicy: RegistrationPolicy::Blocked,
      approvalPolicy: ApprovalPolicy::Required,
    );

    $this->assertSame(UserStatus::Active, $user->getStatus());
  }

  private function createFactory(bool $allowRegistration, bool $approvalRequired = false): UserFactory {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')->willReturnCallback(fn(string $key) => match ($key) {
      'user.allowRegistration' => $allowRegistration,
      'user.approvalRequired' => $approvalRequired,
      default => null,
    });

    return new UserFactory($configProvider, $this->createUserCreationContext());
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

  private function createCredentials(): Credentials {
    return Credentials::create(
      Email::fromString('user@example.com'),
      Username::fromString('testuser'),
      HashedPassword::encode('password123'),
    );
  }

  private function createDisplayName(): DisplayName {
    return DisplayName::fromString('Test User');
  }
}
