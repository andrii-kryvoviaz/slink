<?php

declare(strict_types=1);

namespace Slik\User\Domain\Repository;


use Slik\User\Domain\ValueObject\Email;

interface GetUserCredentialsByEmailInterface {
  
  /**
   * @param Email $email
   * @return array<int, mixed>
   */
  public function getCredentialsByEmail(Email $email): array;
}