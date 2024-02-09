<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Infrastructure\ReadModel\View\UserView;

interface UserRepositoryInterface extends ServiceEntityRepositoryInterface {
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function oneByEmail(Email $email): UserView;
  
  public function add(UserView $userView): void;
}