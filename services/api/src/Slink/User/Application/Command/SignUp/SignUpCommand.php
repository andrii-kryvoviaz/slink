<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SignUp;

use SensitiveParameter;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Validator\Constraints as Assert;
use Slink\User\Infrastructure\Validator\PasswordComplexity;

final readonly class SignUpCommand implements CommandInterface {
  
  private ID $id;

  public function __construct(
    #[SensitiveParameter]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email,

    #[SensitiveParameter]
    #[PasswordComplexity]
    private string $password,

    #[SensitiveParameter]
    #[Assert\NotBlank]
    #[Assert\IdenticalTo(propertyPath: 'password', message: 'Passwords do not match.')]
    private string $confirm,
    
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 30)]
    #[Assert\Regex(pattern: '/^[a-z0-9_\-\.]+$/', message: 'Username can only contain lowercase letters, numbers, underscores, hyphens, and periods.')]
    #[Assert\Regex(pattern: '/^(?!.*(_|-|\.){2})/', message: 'Username cannot contain consecutive characters of the same type.')]
    #[Assert\NotEqualTo(propertyPath: 'email', message: 'Username cannot be the same as email.')]
    #[Assert\Regex(pattern: '/^(?!anonymous$)/i', message: '`Anonymous` is a reserved username.')]
    private string $username
  ) {
    $this->id = ID::generate();
  }
  
  public function getId(): ID {
    return $this->id;
  }

  public function getEmail(): string {
    return $this->email;
  }

  public function getPassword(): string {
    return $this->password;
  }
  
  public function getConfirm(): string {
    return $this->confirm;
  }

  public function getUsername(): string {
    return $this->username;
  }
}