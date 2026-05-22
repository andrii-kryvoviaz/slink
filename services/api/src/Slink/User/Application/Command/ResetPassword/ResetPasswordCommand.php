<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\ResetPassword;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\User\Infrastructure\Validator\PasswordComplexity;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetPasswordCommand implements CommandInterface {
  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email,

    #[\SensitiveParameter]
    #[PasswordComplexity]
    private string $password,
  ) {}

  public function getEmail(): string {
    return $this->email;
  }

  public function getPassword(): string {
    return $this->password;
  }
}
