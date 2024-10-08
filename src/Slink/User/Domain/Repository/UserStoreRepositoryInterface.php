<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;

interface UserStoreRepositoryInterface {
  public function get(ID $id): User;
  
  public function getByUsername(Email|Username $username): ?User;
  
  public function getByRefreshToken(HashedRefreshToken $hashedRefreshToken): ?User;

  public function store(User $user): void;
}