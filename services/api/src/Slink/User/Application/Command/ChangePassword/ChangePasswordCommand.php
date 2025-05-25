<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\ChangePassword;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangePasswordCommand implements CommandInterface {
  use EnvelopedMessage;
  
  public function __construct(
    #[\SensitiveParameter]
    #[Assert\NotBlank]
    private string $old_password,
    
    #[\SensitiveParameter]
    #[Assert\NotBlank]
    #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM)]
    private string $password,
    
    #[\SensitiveParameter]
    #[Assert\NotBlank]
    #[Assert\IdenticalTo(propertyPath: 'password', message: 'Passwords do not match.')]
    private string $confirm,
  ) {}
  
  public function getOldPassword(): string {
    return $this->old_password;
  }
  
  public function getPassword(): string {
    return $this->password;
  }
  
  public function getConfirm(): string {
    return $this->confirm;
  }
}