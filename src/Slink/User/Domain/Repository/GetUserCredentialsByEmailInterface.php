<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;


use Slink\User\Domain\ValueObject\Email;

interface GetUserCredentialsByEmailInterface {
  
  /**
   * @param Email $email
   * @return array<int, mixed>
   */
  public function getCredentialsByEmail(Email $email): array;
}