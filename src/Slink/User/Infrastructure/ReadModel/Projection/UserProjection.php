<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\User\Domain\Event\Role\UserGrantedRole;
use Slink\User\Domain\Event\Role\UserRevokedRole;
use Slink\User\Domain\Event\UserPasswordWasChanged;
use Slink\User\Domain\Event\UserStatusWasChanged;
use Slink\User\Domain\Event\UserWasCreated;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\UserRoleView;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class UserProjection extends AbstractProjection {
  /**
   * @param UserRepositoryInterface $repository
   * @param EntityManagerInterface $entityManager
   */
  public function __construct(
    private readonly UserRepositoryInterface $repository,
    private readonly EntityManagerInterface $entityManager
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
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function handleUserStatusWasChanged(UserStatusWasChanged $event): void {
    $user = $this->repository->one($event->id);
    $user->setStatus($event->status);

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
  }
}