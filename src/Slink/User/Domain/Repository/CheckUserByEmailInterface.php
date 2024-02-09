<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;


use Ramsey\Uuid\UuidInterface;
use Slink\User\Domain\ValueObject\Email;

interface CheckUserByEmailInterface {
    public function existsEmail(Email $email): ?UuidInterface;
}
