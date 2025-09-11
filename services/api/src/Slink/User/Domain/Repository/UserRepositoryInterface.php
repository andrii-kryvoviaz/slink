<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Domain\Filter\UserListFilter;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\ReadModel\View\UserView;

interface UserRepositoryInterface extends ServiceEntityRepositoryInterface {
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function oneByEmail(Email $email): UserView;
  
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function one(ID $id): UserView;
  
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function oneByUsername(Username $username): UserView;
  
  /**
   * @param int $page
   * @param UserListFilter $filter
   * @return Paginator<UserView>
   */
  public function getUserList(int $page, UserListFilter $filter): Paginator;
  
  public function save(UserView $userView): void;
}