<?php

declare(strict_types=1);

namespace Slik\User\Application\Command\SignIn;

use SensitiveParameter;
use Slik\Shared\Application\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SignInCommand implements CommandInterface {
  
  public function __construct(
    #[SensitiveParameter]
    #[Assert\NotBlank]
    private string $username,

    #[SensitiveParameter]
    #[Assert\NotBlank]
    private string $password,
  ) {}
  
  public function getUsername(): string {
    return $this->username;
  }
  
  public function getPassword(): string {
    return $this->password;
  }
}