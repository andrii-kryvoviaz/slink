<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateUser;

use SensitiveParameter;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Infrastructure\Validator\PasswordComplexity;
use Slink\User\Infrastructure\Validator\Username;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateUserCommand implements CommandInterface {
  private ID $id;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email,

    #[SensitiveParameter]
    #[PasswordComplexity]
    private string $password,

    #[Assert\NotBlank]
    #[Username]
    #[Assert\NotEqualTo(propertyPath: 'email', message: 'Username cannot be the same as email.')]
    private string $username,

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 100)]
    private string $displayName,

    private bool   $activate = false
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

  public function getUsername(): string {
    return $this->username;
  }

  public function getDisplayName(): string {
    return $this->displayName;
  }

  public function isActivate(): bool {
    return $this->activate;
  }
}
