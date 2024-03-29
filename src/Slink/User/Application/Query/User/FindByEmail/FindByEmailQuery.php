<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\User\FindByEmail;

use Slink\Shared\Application\Query\QueryInterface;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class FindByEmailQuery implements QueryInterface {
  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email,
  ) {
  }
  
  public function getEmail(): string {
    return $this->email;
  }
}