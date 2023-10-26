<?php

declare(strict_types=1);

namespace Slik\User\Domain\Repository;


use Ramsey\Uuid\UuidInterface;
use Slik\User\Domain\ValueObject\Email;

interface CheckUserByEmailInterface {
    public function existsEmail(Email $email): ?UuidInterface;
}
