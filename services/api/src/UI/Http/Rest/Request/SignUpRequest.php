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
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email,

    #[SensitiveParameter]
    #[PasswordComplexity]
    private string $password,

    #[SensitiveParameter] // @phpstan-ignore property.onlyWritten
    #[Assert\NotBlank]
    #[Assert\IdenticalTo(propertyPath: 'password', message: 'Passwords do not match.')]
    private string $confirm,

    #[Assert\NotBlank]
    #[UsernameConstraint]
    #[Assert\NotEqualTo(propertyPath: 'email', message: 'Username cannot be the same as email.')]
    private string $username
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
