<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Contracts\OAuthProviderProfile;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\Factory\OAuthUserFactory;
use Slink\User\Domain\Factory\UserFactory;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Slink\User\Domain\ValueObject\Username;

final class OAuthUserFactoryTest extends TestCase {

  #[Test]
  public function itCreatesUserWithClaimsData(): void {
    $identity = $this->createIdentity();

    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $factory = new OAuthUserFactory($this->createRealUserFactory(), $uniqueUsernameSpec);

    $result = $factory->create($identity);

    $this->assertInstanceOf(User::class, $result);
  }

  #[Test]
  public function itHandlesUsernameCollision(): void {
    $identity = $this->createIdentity();

    $callCount = 0;
    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')
      ->willReturnCallback(function (Username $username) use (&$callCount) {
        $callCount++;
        if ($callCount === 1) {
          return false;
        }
        return true;
      });

    $factory = new OAuthUserFactory($this->createRealUserFactory(), $uniqueUsernameSpec);

    $result = $factory->create($identity);

    $this->assertInstanceOf(User::class, $result);
    $this->assertGreaterThan(1, $callCount);
  }

  #[Test]
  public function itForwardsProviderPoliciesToUserFactory(): void {
    $identity = $this->createIdentity();

    $provider = $this->createStub(OAuthProviderProfile::class);
    $provider->method('getRegistrationPolicy')->willReturn(RegistrationPolicy::Allowed);
    $provider->method('getApprovalPolicy')->willReturn(ApprovalPolicy::Required);

    $userFactory = $this->createMock(UserFactory::class);
    $userFactory->expects($this->once())
      ->method('create')
      ->with(
        $this->isInstanceOf(ID::class),
        $this->isInstanceOf(Credentials::class),
        $identity->getDisplayName(),
        null,
        RegistrationPolicy::Allowed,
        ApprovalPolicy::Required,
      );

    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $factory = new OAuthUserFactory($userFactory, $uniqueUsernameSpec);

    $factory->create($identity, $provider);
  }

  #[Test]
  public function itDefaultsPoliciesToInheritWhenProviderIsMissing(): void {
    $identity = $this->createIdentity();

    $userFactory = $this->createMock(UserFactory::class);
    $userFactory->expects($this->once())
      ->method('create')
      ->with(
        $this->isInstanceOf(ID::class),
        $this->isInstanceOf(Credentials::class),
        $identity->getDisplayName(),
        null,
        RegistrationPolicy::Inherit,
        ApprovalPolicy::Inherit,
      );

    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $factory = new OAuthUserFactory($userFactory, $uniqueUsernameSpec);

    $factory->create($identity);
  }

  private function createIdentity(string $displayName = 'Test User'): OAuthIdentity {
    $identity = $this->createStub(OAuthIdentity::class);
    $identity->method('getEmail')->willReturn(Email::fromString('user@example.com'));
    $identity->method('getDisplayName')->willReturn(DisplayName::fromString($displayName));
    $identity->method('getSubject')->willReturn($this->createStub(OAuthSubject::class));

    return $identity;
  }

  private function createRealUserFactory(): UserFactory {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')->willReturnCallback(fn(string $key) => match ($key) {
      'user.allowRegistration' => true,
      'user.approvalRequired' => false,
      default => null,
    });

    $uniqueEmailSpec = $this->createStub(UniqueEmailSpecificationInterface::class);
    $uniqueEmailSpec->method('isUnique')->willReturn(true);

    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $uniqueDisplayNameSpec = $this->createStub(UniqueDisplayNameSpecificationInterface::class);
    $uniqueDisplayNameSpec->method('isUnique')->willReturn(true);

    $context = new UserCreationContext($uniqueEmailSpec, $uniqueUsernameSpec, $uniqueDisplayNameSpec);

    return new UserFactory($configProvider, $context);
  }
}
