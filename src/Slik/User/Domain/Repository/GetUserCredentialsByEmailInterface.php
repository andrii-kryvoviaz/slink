<?php

declare(strict_types=1);

namespace Slik\User\Domain\Repository;


use Slik\User\Domain\ValueObject\Email;

interface GetUserCredentialsByEmailInterface {
  public function getCredentialsByEmail(Email $email): array;
}