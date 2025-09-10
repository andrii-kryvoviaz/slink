<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateDemoUser;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateDemoUserCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    public string $username,
    
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    public string $password,
    
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 100)]
    public string $displayName,
    
    #[Assert\Email]
    public string $email = 'demo@demo.local'
  ) {}
}
