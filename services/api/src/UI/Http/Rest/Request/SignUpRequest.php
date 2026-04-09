<?php

declare(strict_types=1);

namespace UI\Http\Rest\Request;

use SensitiveParameter;
use Slink\User\Application\Command\CreateUser\CreateUserCommand;
use Slink\User\Infrastructure\Validator\PasswordComplexity;
use Slink\User\Infrastructure\Validator\Username as UsernameConstraint;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SignUpRequest {
  public function __construct(
    #[SensitiveParameter]
    #[Assert\NotBlank(message: 'VALIDATION_REQUIRED')]
    #[Assert\Email(message: 'EMAIL_INVALID')]
    public string $email,

    #[SensitiveParameter]
    #[PasswordComplexity]
    public string $password,

    #[SensitiveParameter]
    #[Assert\NotBlank(message: 'VALIDATION_REQUIRED')]
    #[Assert\IdenticalTo(propertyPath: 'password', message: 'AUTH_PASSWORDS_MISMATCH')]
    public string $confirm,

    #[Assert\NotBlank(message: 'VALIDATION_REQUIRED')]
    #[UsernameConstraint]
    #[Assert\NotEqualTo(propertyPath: 'email', message: 'AUTH_USERNAME_EQUALS_EMAIL')]
    public string $username
  ) {}

  public function toCommand(): CreateUserCommand {
    return new CreateUserCommand(
      email: $this->email,
      password: $this->password,
      username: $this->username,
      displayName: $this->username,
    );
  }
}
