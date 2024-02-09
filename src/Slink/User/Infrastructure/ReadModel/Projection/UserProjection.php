<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Projection;

use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\User\Domain\Event\UserWasCreated;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class UserProjection extends AbstractProjection {
  public function __construct(private readonly UserRepositoryInterface $repository) {
  }

  public function handleUserWasCreated(UserWasCreated $event): void {
    $user = UserView::fromEvent($event);

    $this->repository->add($user);
  }
}