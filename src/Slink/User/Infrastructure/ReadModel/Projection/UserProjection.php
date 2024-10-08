<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\User\Domain\Event\UserDisplayNameWasChanged;
use Slink\User\Domain\Event\UserPasswordWasChanged;
use Slink\User\Domain\Event\UserStatusWasChanged;
use Slink\User\Domain\Event\UserWasCreated;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class UserProjection extends AbstractProjection {
  /**
   * @param UserRepositoryInterface $repository
   */
  public function __construct(private readonly UserRepositoryInterface $repository) {
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
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function handleUserDisplayNameWasChanged(UserDisplayNameWasChanged $event): void {
    $user = $this->repository->one($event->id);
    $user->setDisplayName($event->displayName);

    $this->repository->save($user);
  }
}