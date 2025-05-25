<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Ramsey\Uuid\UuidInterface;
use Slink\User\Domain\ValueObject\Username;

interface CheckUserByUsernameInterface {
  public function existsUsername(Username $username): ?UuidInterface;
}