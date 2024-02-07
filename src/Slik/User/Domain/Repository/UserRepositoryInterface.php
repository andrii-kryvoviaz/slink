<?php

declare(strict_types=1);

namespace Slik\User\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
use Slik\User\Domain\ValueObject\Email;
use Slik\User\Infrastructure\ReadModel\View\UserView;

interface UserRepositoryInterface extends ServiceEntityRepositoryInterface {
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function oneByEmail(Email $email): UserView;
  
  public function add(UserView $userView): void;
}