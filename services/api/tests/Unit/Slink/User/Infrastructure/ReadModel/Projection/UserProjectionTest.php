<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\ReadModel\Projection\UserProjection;
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
  public function itScrubsUserOnDeletedStatus(): void {
    $id = ID::generate();
    $userId = $id->toString();
    $expectedUsername = sprintf('purged_%s', substr(str_replace('-', '', $userId), 0, 23));

    $user = $this->createMock(UserView::class);
    $user->expects($this->once())->method('setStatus')->with(UserStatus::Deleted);
    $user->expects($this->once())->method('clearRoles');
    $user->expects($this->once())
      ->method('scrub')
      ->with(
        $this->callback(fn(Email $email) => $email->toString() === sprintf('purged-%s@purged.local', $userId)),
        $this->callback(fn(Username $username) => $username->toString() === $expectedUsername),
        $this->isInstanceOf(HashedPassword::class),
      );

    $this->repository->expects($this->once())->method('one')->with($id)->willReturn($user);
    $this->repository->expects($this->once())->method('save')->with($user);

    $this->refreshTokenRepository->expects($this->once())->method('deleteByUserId')->with($userId);
    $this->apiKeyRepository->expects($this->once())->method('deleteByUserId')->with($id);
    $this->oauthLinkRepository->expects($this->once())->method('deleteByUserId')->with($userId);
    $this->userRoleManager->expects($this->once())->method('storePermissionsVersion')->with($userId);
    $this->preferencesRepository->expects($this->never())->method('deleteByUserId');

    $this->createProjection()->handleUserStatusWasChanged(new UserStatusWasChanged($id, UserStatus::Deleted));
  }

  #[Test]
  public function itDoesNotScrubUserOnNonDeletedStatus(): void {
    $id = ID::generate();

    $user = $this->createMock(UserView::class);
    $user->expects($this->once())->method('setStatus')->with(UserStatus::Suspended);
    $user->expects($this->never())->method('clearRoles');
    $user->expects($this->never())->method('scrub');

    $this->repository->expects($this->once())->method('one')->with($id)->willReturn($user);
    $this->repository->expects($this->once())->method('save')->with($user);

    $this->refreshTokenRepository->expects($this->never())->method('deleteByUserId');
    $this->apiKeyRepository->expects($this->never())->method('deleteByUserId');
    $this->oauthLinkRepository->expects($this->never())->method('deleteByUserId');
    $this->preferencesRepository->expects($this->never())->method('deleteByUserId');
    $this->userRoleManager->expects($this->never())->method('storePermissionsVersion');

    $this->createProjection()->handleUserStatusWasChanged(new UserStatusWasChanged($id, UserStatus::Suspended));
  }

  #[Test]
  public function itDeletesPreferencesOnUserPurged(): void {
    $id = ID::generate();

    $this->preferencesRepository->expects($this->once())
      ->method('deleteByUserId')
      ->with($id->toString());

    $this->repository->expects($this->never())->method('one');
    $this->repository->expects($this->never())->method('save');
    $this->refreshTokenRepository->expects($this->never())->method('deleteByUserId');
    $this->apiKeyRepository->expects($this->never())->method('deleteByUserId');
    $this->oauthLinkRepository->expects($this->never())->method('deleteByUserId');
    $this->userRoleManager->expects($this->never())->method('storePermissionsVersion');

    $this->createProjection()->handleUserWasPurged(new UserWasPurged($id));
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
