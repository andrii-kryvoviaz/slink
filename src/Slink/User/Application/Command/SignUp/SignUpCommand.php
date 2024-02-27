<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SignUp;

use SensitiveParameter;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SignUpCommand implements CommandInterface {
  
  private ID $id;

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
    #[Assert\IdenticalTo(propertyPath: 'password', message: 'Passwords do not match.')]
    private string $confirm,

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 30)]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_\-\s\.]+$/', message: 'Display name can only contain letters, numbers, underscores, hyphens, spaces, and periods.')]
    #[Assert\Regex(pattern: '/^(?!.*(_|-|\s\.){2})/', message: 'Display name cannot contain consecutive characters of the same type.')]
    #[Assert\NotEqualTo(propertyPath: 'email', message: 'Display name cannot be the same as email.')]
    #[Assert\Regex(pattern: '/^(?!anonymous$)/i', message: '`Anonymous` is a reserved display name.')]
    private string $display_name,
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

  public function getDisplayName(): string {
    return $this->display_name;
  }
}