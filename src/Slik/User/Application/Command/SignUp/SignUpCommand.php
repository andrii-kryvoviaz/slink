<?php

declare(strict_types=1);

namespace Slik\User\Application\Command\SignUp;

use SensitiveParameter;
use Slik\Shared\Application\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SignUpCommand implements CommandInterface {

  public function __construct(
    #[SensitiveParameter]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email,

    #[SensitiveParameter]
    #[Assert\NotBlank]
    #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM)]
    private string $password,

    #[SensitiveParameter]
    #[Assert\NotBlank]
    #[Assert\IdenticalTo(propertyPath: 'password', message: 'Passwords do not match')]
    private string $confirm,

    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    private string $displayName,
  ) {}

  public function getEmail(): string {
    return $this->email;
  }

  public function getPassword(): string {
    return $this->password;
  }
  
  public function getConfirm(): string {
    return $this->confirm;
  }

  public function getDisplayName(): string {
    return $this->displayName;
  }
}