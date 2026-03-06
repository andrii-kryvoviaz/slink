<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateUser;

use SensitiveParameter;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\Validator\PasswordComplexity;
use Slink\User\Infrastructure\Validator\Username as UsernameConstraint;
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
    #[UsernameConstraint]
    #[Assert\NotEqualTo(propertyPath: 'email', message: 'Username cannot be the same as email.')]
    private string $username,

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 100)]
    private string $displayName,

    private ?UserStatus $status = null
  ) {
    $this->id = ID::generate();
  }

  public function getId(): ID {
    return $this->id;
  }

  public function getCredentials(): Credentials {
    return Credentials::create(
      Email::fromString($this->email),
      Username::fromString($this->username),
      HashedPassword::encode($this->password),
    );
  }

  public function getDisplayName(): DisplayName {
    return DisplayName::fromString($this->displayName);
  }

  public function getStatus(): ?UserStatus {
    return $this->status;
  }
}
