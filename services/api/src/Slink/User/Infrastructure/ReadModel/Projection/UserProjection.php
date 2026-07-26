<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\User\Application\Service\UserRoleManagerInterface;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Event\Role\UserGrantedRole;
use Slink\User\Domain\Event\Role\UserRevokedRole;
use Slink\User\Domain\Event\UserDisplayNameWasChanged;
use Slink\User\Domain\Event\UserPasswordWasChanged;
use Slink\User\Domain\Event\UserPasswordWasReset;
use Slink\User\Domain\Event\UserPreferencesWasUpdated;
use Slink\User\Domain\Event\UserStatusWasChanged;
use Slink\User\Domain\Event\UserWasCreated;
use Slink\User\Domain\Event\UserWasPurged;
use Slink\User\Domain\Repository\ApiKeyRepositoryInterface;
use Slink\User\Domain\Repository\OAuthLinkRepositoryInterface;
use Slink\User\Domain\Repository\RefreshTokenRepositoryInterface;
use Slink\User\Domain\Repository\UserPreferencesRepositoryInterface;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\UserPreferencesView;
use Slink\User\Infrastructure\ReadModel\View\UserRoleView;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class UserProjection extends AbstractProjection {
  public function __construct(
    private readonly UserRepositoryInterface $repository,
    private readonly UserRoleManagerInterface $userRoleManager,
    private readonly EntityManagerInterface $entityManager,
    private readonly UserPreferencesRepositoryInterface $preferencesRepository,
    private readonly RefreshTokenRepositoryInterface $refreshTokenRepository,
    private readonly ApiKeyRepositoryInterface $apiKeyRepository,
    private readonly OAuthLinkRepositoryInterface $oauthLinkRepository,
  ) {
  }
  
  /**
   * @param UserWasCreated $event
   * @return void
   */
  public function handleUserWasCreated(UserWasCreated $event): void {
    $user = UserView::fromEvent($event);

    $this->repository->save($user);
  }
  
  /**
   * @param UserPasswordWasChanged $event
   * @return void
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function handleUserPasswordWasChanged(UserPasswordWasChanged $event): void {
    $user = $this->repository->one($event->id);
    $user->setPassword($event->password);

    $this->repository->save($user);
  }

  /**
   * @param UserPasswordWasReset $event
   * @return void
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function handleUserPasswordWasReset(UserPasswordWasReset $event): void {
    $user = $this->repository->one($event->id);
    $user->setPassword($event->password);

    $this->repository->save($user);
  }

  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function handleUserStatusWasChanged(UserStatusWasChanged $event): void {
    $user = $this->repository->one($event->id);
    $user->setStatus($event->status);

    if ($event->status === UserStatus::Deleted) {
      $this->revokeAccess($user, $event->id);
    }

    $this->repository->save($user);
  }
  
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function handleUserDisplayNameWasChanged(UserDisplayNameWasChanged $event): void {
    $user = $this->repository->one($event->id);
    $user->setDisplayName($event->displayName);

    $this->repository->save($user);
  }
  
  /**
   * @param UserGrantedRole $event
   * @return void
   * @throws NonUniqueResultException
   * @throws NotFoundException
   * @throws ORMException
   */
  public function handleUserGrantedRole(UserGrantedRole $event): void {
    $user = $this->repository->one($event->id);
    
    $role = $event->role->getRole();
    $roleReference = $this->entityManager->getReference(UserRoleView::class, $role);
    
    if(!$roleReference instanceof UserRoleView) {
      throw new NotFoundException();
    }
    
    $user->addRole($roleReference);
    $this->repository->save($user);
    
    $this->userRoleManager->storePermissionsVersion($event->id->toString(), time());
  }
  
  /**
   * @param UserRevokedRole $event
   * @return void
   * @throws NonUniqueResultException
   * @throws NotFoundException
   * @throws ORMException
   */
  public function handleUserRevokedRole(UserRevokedRole $event): void {
    $user = $this->repository->one($event->id);
    
    $role = $event->role->getRole();
    $roleReference = $this->entityManager->getReference(UserRoleView::class, $role);
    
    if(!$roleReference instanceof UserRoleView) {
      throw new NotFoundException();
    }
    
    $user->removeRole($roleReference);
    $this->repository->save($user);
    
    $this->userRoleManager->storePermissionsVersion($event->id->toString(), time());
  }
  
  /**
   * @param UserWasPurged $event
   * @return void
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function handleUserWasPurged(UserWasPurged $event): void {
    $user = $this->repository->one($event->id);

    $this->revokeAccess($user, $event->id);
    $this->repository->save($user);

    $this->preferencesRepository->deleteByUserId($event->id->toString());
  }

  /**
   * @param UserView $user
   * @param ID $id
   * @return void
   */
  private function revokeAccess(UserView $user, ID $id): void {
    $userId = $id->toString();

    $this->refreshTokenRepository->deleteByUserId($userId);
    $this->apiKeyRepository->deleteByUserId($id);
    $this->oauthLinkRepository->deleteByUserId($userId);

    $user->clearRoles();
    $user->revokeIdentity();

    $this->userRoleManager->storePermissionsVersion($userId, time());
  }

  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function handleUserPreferencesWasUpdated(UserPreferencesWasUpdated $event): void {
    $userId = $event->id->toString();
    $existingPrefs = $this->preferencesRepository->findByUserId($userId);
    
    if ($existingPrefs) {
      $existingPrefs->setPreferences($event->preferences);
      $this->preferencesRepository->save($existingPrefs);
    } else {
      $prefs = UserPreferencesView::create($userId, $event->preferences);
      $this->preferencesRepository->save($prefs);
    }
  }
}