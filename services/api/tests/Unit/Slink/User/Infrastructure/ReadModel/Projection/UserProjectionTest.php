<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\ReadModel\Projection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Service\UserRoleManagerInterface;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Event\UserStatusWasChanged;
use Slink\User\Domain\Event\UserWasPurged;
use Slink\User\Domain\Repository\ApiKeyRepositoryInterface;
use Slink\User\Domain\Repository\OAuthLinkRepositoryInterface;
use Slink\User\Domain\Repository\RefreshTokenRepositoryInterface;
use Slink\User\Domain\Repository\UserPreferencesRepositoryInterface;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\Auth\PermissionsVersion;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\ReadModel\Projection\UserProjection;
use Slink\User\Infrastructure\ReadModel\View\UserRoleView;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class UserProjectionTest extends TestCase {
  private UserRepositoryInterface&MockObject $repository;
  private UserRoleManagerInterface&MockObject $userRoleManager;
  private UserPreferencesRepositoryInterface&MockObject $preferencesRepository;
  private RefreshTokenRepositoryInterface&MockObject $refreshTokenRepository;
  private ApiKeyRepositoryInterface&MockObject $apiKeyRepository;
  private OAuthLinkRepositoryInterface&MockObject $oauthLinkRepository;

  protected function setUp(): void {
    $this->repository = $this->createMock(UserRepositoryInterface::class);
    $this->userRoleManager = $this->createMock(UserRoleManagerInterface::class);
    $this->preferencesRepository = $this->createMock(UserPreferencesRepositoryInterface::class);
    $this->refreshTokenRepository = $this->createMock(RefreshTokenRepositoryInterface::class);
    $this->apiKeyRepository = $this->createMock(ApiKeyRepositoryInterface::class);
    $this->oauthLinkRepository = $this->createMock(OAuthLinkRepositoryInterface::class);
  }

  #[Test]
  public function itRevokesAccessOnDeletedStatus(): void {
    $id = ID::generate();
    $userId = $id->toString();

    $user = $this->createMock(UserView::class);
    $user->expects($this->once())->method('setStatus')->with(UserStatus::Deleted);
    $user->expects($this->once())->method('clearRoles');
    $user->expects($this->once())->method('revokeIdentity');

    $this->repository->expects($this->once())->method('one')->with($id)->willReturn($user);
    $this->repository->expects($this->once())->method('save')->with($user);

    $this->refreshTokenRepository->expects($this->once())->method('deleteByUserId')->with($userId);
    $this->apiKeyRepository->expects($this->once())->method('deleteByUserId')->with($id);
    $this->oauthLinkRepository->expects($this->once())->method('deleteByUserId')->with($userId);
    $this->userRoleManager->expects($this->once())
      ->method('storePermissionsVersion')
      ->with($userId, PermissionsVersion::terminal());
    $this->preferencesRepository->expects($this->never())->method('deleteByUserId');

    $this->createProjection()->handleUserStatusWasChanged(new UserStatusWasChanged($id, UserStatus::Deleted));
  }

  #[Test]
  public function itBumpsPermissionsVersionWithoutRevokingAccessOnNonDeletedStatus(): void {
    $id = ID::generate();
    $now = time();

    $user = $this->createMock(UserView::class);
    $user->expects($this->once())->method('setStatus')->with(UserStatus::Suspended);
    $user->expects($this->never())->method('clearRoles');
    $user->expects($this->never())->method('revokeIdentity');

    $this->repository->expects($this->once())->method('one')->with($id)->willReturn($user);
    $this->repository->expects($this->once())->method('save')->with($user);

    $this->refreshTokenRepository->expects($this->never())->method('deleteByUserId');
    $this->apiKeyRepository->expects($this->never())->method('deleteByUserId');
    $this->oauthLinkRepository->expects($this->never())->method('deleteByUserId');
    $this->preferencesRepository->expects($this->never())->method('deleteByUserId');
    $this->userRoleManager->expects($this->once())
      ->method('storePermissionsVersion')
      ->with(
        $id->toString(),
        $this->callback(
          static fn (PermissionsVersion $version): bool => $version->invalidates($now - 1)
            && !$version->invalidates($now + 3600),
        ),
      );

    $this->createProjection()->handleUserStatusWasChanged(new UserStatusWasChanged($id, UserStatus::Suspended));
  }

  #[Test]
  public function itRevokesAccessAndDeletesPreferencesOnUserPurged(): void {
    $id = ID::generate();
    $userId = $id->toString();

    $user = $this->createMock(UserView::class);
    $user->expects($this->never())->method('setStatus');
    $user->expects($this->once())->method('clearRoles');
    $user->expects($this->once())->method('revokeIdentity');

    $this->repository->expects($this->once())->method('one')->with($id)->willReturn($user);
    $this->repository->expects($this->once())->method('save')->with($user);

    $this->refreshTokenRepository->expects($this->once())->method('deleteByUserId')->with($userId);
    $this->apiKeyRepository->expects($this->once())->method('deleteByUserId')->with($id);
    $this->oauthLinkRepository->expects($this->once())->method('deleteByUserId')->with($userId);
    $this->userRoleManager->expects($this->once())
      ->method('storePermissionsVersion')
      ->with($userId, PermissionsVersion::terminal());

    $this->preferencesRepository->expects($this->once())
      ->method('deleteByUserId')
      ->with($userId);

    $this->createProjection()->handleUserWasPurged(new UserWasPurged($id));
  }

  #[Test]
  public function itRevokesAccessIdempotentlyAcrossStatusChangeAndRepeatedPurge(): void {
    $id = ID::generate();
    $userId = $id->toString();
    $user = $this->createRealUser($id);

    $this->repository->expects($this->exactly(3))->method('one')->with($id)->willReturn($user);
    $this->repository->expects($this->exactly(3))->method('save')->with($user);
    $this->preferencesRepository->expects($this->exactly(2))->method('deleteByUserId')->with($userId);
    $this->refreshTokenRepository->expects($this->exactly(3))->method('deleteByUserId')->with($userId);
    $this->apiKeyRepository->expects($this->exactly(3))->method('deleteByUserId')->with($id);
    $this->oauthLinkRepository->expects($this->exactly(3))->method('deleteByUserId')->with($userId);
    $this->userRoleManager->expects($this->exactly(3))
      ->method('storePermissionsVersion')
      ->with($userId, PermissionsVersion::terminal());

    $projection = $this->createProjection();

    $projection->handleUserStatusWasChanged(new UserStatusWasChanged($id, UserStatus::Deleted));
    $projection->handleUserWasPurged(new UserWasPurged($id));
    $projection->handleUserWasPurged(new UserWasPurged($id));

    self::assertNull($user->getEmail());
    self::assertNull($user->getUsername());
    self::assertSame('Member', $user->getDisplayName());
    self::assertSame([], $user->getRoles());
  }

  private function createRealUser(ID $id): UserView {
    $role = new UserRoleView('ROLE_USER', 'User');

    return new UserView(
      $id->toString(),
      Email::fromString('member@example.com'),
      Username::fromString('member'),
      DisplayName::fromString('Member'),
      HashedPassword::encode('password123'),
      DateTime::now(),
      null,
      UserStatus::Active,
      new ArrayCollection([$role]),
    );
  }

  private function createProjection(): UserProjection {
    return new UserProjection(
      $this->repository,
      $this->userRoleManager,
      $this->createStub(EntityManagerInterface::class),
      $this->preferencesRepository,
      $this->refreshTokenRepository,
      $this->apiKeyRepository,
      $this->oauthLinkRepository,
    );
  }
}
