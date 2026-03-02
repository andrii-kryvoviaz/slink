<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Factory\OAuthUserFactory;
use Slink\User\Domain\Factory\UserFactory;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\OAuthClaims;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Slink\User\Domain\ValueObject\Username;

final class OAuthUserFactoryTest extends TestCase {

  #[Test]
  public function itCreatesUserWithClaimsData(): void {
    $email = Email::fromString('user@example.com');
    $displayName = DisplayName::fromString('Test User');
    $subject = $this->createStub(OAuthSubject::class);

    $claims = $this->createStub(OAuthClaims::class);
    $claims->method('getEmail')->willReturn($email);
    $claims->method('getDisplayName')->willReturn($displayName);
    $claims->method('getSubject')->willReturn($subject);

    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $userFactory = $this->createStubUserFactory();

    $factory = new OAuthUserFactory($userFactory, $uniqueUsernameSpec);

    $result = $factory->create($claims);

    $this->assertInstanceOf(User::class, $result);
  }

  #[Test]
  public function itHandlesUsernameCollision(): void {
    $email = Email::fromString('user@example.com');
    $displayName = DisplayName::fromString('Test User');
    $subject = $this->createStub(OAuthSubject::class);

    $claims = $this->createStub(OAuthClaims::class);
    $claims->method('getEmail')->willReturn($email);
    $claims->method('getDisplayName')->willReturn($displayName);
    $claims->method('getSubject')->willReturn($subject);

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

    $userFactory = $this->createStubUserFactory();

    $factory = new OAuthUserFactory($userFactory, $uniqueUsernameSpec);

    $result = $factory->create($claims);

    $this->assertInstanceOf(User::class, $result);
    $this->assertGreaterThan(1, $callCount);
  }

  private function createStubUserFactory(): UserFactory {
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
