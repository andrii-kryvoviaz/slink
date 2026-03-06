<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Service\OAuthUserResolver;
use Slink\User\Domain\Exception\OAuthEmailNotVerifiedException;
use Slink\User\Domain\Exception\OAuthEmailRequiredException;
use Slink\User\Domain\Factory\OAuthUserFactory;
use Slink\User\Domain\Repository\CheckUserByEmailInterface;
use Slink\User\Domain\Repository\OAuthLinkRepositoryInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Slink\User\Infrastructure\ReadModel\View\OAuthLinkView;

final class OAuthUserResolverTest extends TestCase {

  #[Test]
  public function itReturnsUserWhenOauthLinkExists(): void {
    $userId = ID::generate();
    $subject = $this->createStub(OAuthSubject::class);
    $identity = $this->createStub(OAuthIdentity::class);
    $identity->method('getSubject')->willReturn($subject);

    $existingLink = $this->createStub(OAuthLinkView::class);
    $existingLink->method('getUserId')->willReturn($userId->toString());

    $resolver = $this->createResolver(
      linkConfig: function (OAuthLinkRepositoryInterface&MockObject $repo) use ($subject, $existingLink) {
        $repo->expects($this->once())
          ->method('findBySubject')
          ->with($subject)
          ->willReturn($existingLink);
      },
      storeConfig: function (UserStoreRepositoryInterface&MockObject $store) use ($userId) {
        $store->expects($this->once())
          ->method('get')
          ->with($this->callback(fn(ID $id) => $id->toString() === $userId->toString()));
      },
    );

    $result = $resolver->resolve($identity);

    $this->assertInstanceOf(User::class, $result);
  }

  #[Test]
  public function itReturnsExistingUserWhenEmailMatches(): void {
    $existingUserId = Uuid::uuid4();
    $email = Email::fromString('existing@example.com');
    $subject = $this->createStub(OAuthSubject::class);

    $identity = $this->createStub(OAuthIdentity::class);
    $identity->method('getSubject')->willReturn($subject);
    $identity->method('getEmail')->willReturn($email);
    $identity->method('isEmailVerified')->willReturn(true);

    $resolver = $this->createResolver(
      linkConfig: function (OAuthLinkRepositoryInterface&MockObject $repo) {
        $repo->expects($this->once())
          ->method('findBySubject')
          ->willReturn(null);
      },
      storeConfig: function (UserStoreRepositoryInterface&MockObject $store) use ($existingUserId) {
        $store->expects($this->once())
          ->method('get')
          ->with($this->callback(fn(ID $id) => $id->toString() === $existingUserId->toString()));
      },
      emailCheckConfig: function (CheckUserByEmailInterface&MockObject $check) use ($email, $existingUserId) {
        $check->expects($this->once())
          ->method('existsEmail')
          ->with($email)
          ->willReturn($existingUserId);
      },
    );

    $result = $resolver->resolve($identity);

    $this->assertInstanceOf(User::class, $result);
  }

  #[Test]
  public function itThrowsWhenEmailNotVerifiedAndExistingUserFound(): void {
    $existingUserId = Uuid::uuid4();
    $email = Email::fromString('existing@example.com');
    $subject = $this->createStub(OAuthSubject::class);

    $identity = $this->createStub(OAuthIdentity::class);
    $identity->method('getSubject')->willReturn($subject);
    $identity->method('getEmail')->willReturn($email);
    $identity->method('isEmailVerified')->willReturn(false);

    $resolver = $this->createResolver(
      emailCheckConfig: function (CheckUserByEmailInterface&MockObject $check) use ($email, $existingUserId) {
        $check->expects($this->once())
          ->method('existsEmail')
          ->with($email)
          ->willReturn($existingUserId);
      },
    );

    $this->expectException(OAuthEmailNotVerifiedException::class);

    $resolver->resolve($identity);
  }

  #[Test]
  public function itThrowsWhenNoLinkAndNoEmail(): void {
    $subject = $this->createStub(OAuthSubject::class);

    $identity = $this->createStub(OAuthIdentity::class);
    $identity->method('getSubject')->willReturn($subject);
    $identity->method('getEmail')->willReturn(null);

    $resolver = $this->createResolver();

    $this->expectException(OAuthEmailRequiredException::class);

    $resolver->resolve($identity);
  }

  #[Test]
  public function itCreatesNewUserWhenNoLinkAndNoExistingEmail(): void {
    $email = Email::fromString('newuser@example.com');
    $subject = $this->createStub(OAuthSubject::class);

    $identity = $this->createStub(OAuthIdentity::class);
    $identity->method('getSubject')->willReturn($subject);
    $identity->method('getEmail')->willReturn($email);
    $identity->method('getDisplayName')->willReturn(DisplayName::fromString('New User'));
    $identity->method('isEmailVerified')->willReturn(true);

    $resolver = $this->createResolver(
      storeConfig: function (UserStoreRepositoryInterface&MockObject $store) {
        $store->expects($this->never())->method('get');
      },
      emailCheckConfig: function (CheckUserByEmailInterface&MockObject $check) use ($email) {
        $check->expects($this->once())
          ->method('existsEmail')
          ->with($email)
          ->willReturn(null);
      },
      factoryConfig: function (MockObject $factory) use ($identity) {
        $factory->expects($this->once())
          ->method('create')
          ->with($identity);
      },
    );

    $result = $resolver->resolve($identity);

    $this->assertInstanceOf(User::class, $result);
  }

  #[Test]
  public function itThrowsWhenEmailNotVerifiedForNewUser(): void {
    $email = Email::fromString('newuser@example.com');
    $subject = $this->createStub(OAuthSubject::class);

    $identity = $this->createStub(OAuthIdentity::class);
    $identity->method('getSubject')->willReturn($subject);
    $identity->method('getEmail')->willReturn($email);
    $identity->method('isEmailVerified')->willReturn(false);

    $resolver = $this->createResolver(
      emailCheckConfig: function (CheckUserByEmailInterface&MockObject $check) use ($email) {
        $check->expects($this->once())
          ->method('existsEmail')
          ->with($email)
          ->willReturn(null);
      },
    );

    $this->expectException(OAuthEmailNotVerifiedException::class);

    $resolver->resolve($identity);
  }

  private function createResolver(
    ?callable $linkConfig = null,
    ?callable $storeConfig = null,
    ?callable $emailCheckConfig = null,
    ?callable $factoryConfig = null,
  ): OAuthUserResolver {
    if ($linkConfig) {
      $linkRepository = $this->createMock(OAuthLinkRepositoryInterface::class);
      $linkConfig($linkRepository);
    } else {
      $linkRepository = $this->createStub(OAuthLinkRepositoryInterface::class);
    }

    if ($storeConfig) {
      $userStore = $this->createMock(UserStoreRepositoryInterface::class);
      $storeConfig($userStore);
    } else {
      $userStore = $this->createStub(UserStoreRepositoryInterface::class);
    }

    if ($emailCheckConfig) {
      $checkUserByEmail = $this->createMock(CheckUserByEmailInterface::class);
      $emailCheckConfig($checkUserByEmail);
    } else {
      $checkUserByEmail = $this->createStub(CheckUserByEmailInterface::class);
    }

    if ($factoryConfig) {
      $oauthUserFactory = $this->createMock(OAuthUserFactory::class);
      $factoryConfig($oauthUserFactory);
    } else {
      $oauthUserFactory = $this->createStub(OAuthUserFactory::class);
    }

    return new OAuthUserResolver($linkRepository, $userStore, $checkUserByEmail, $oauthUserFactory);
  }
}
