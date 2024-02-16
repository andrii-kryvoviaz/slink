<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SignIn;

use SensitiveParameter;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SignInCommand implements CommandInterface {
  use EnvelopedMessage;
  
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