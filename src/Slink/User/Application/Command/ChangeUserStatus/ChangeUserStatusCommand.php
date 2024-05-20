<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\ChangeUserStatus;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Slink\User\Domain\Enum\UserStatus;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeUserStatusCommand implements CommandInterface {
  use EnvelopedMessage;
  
  /**
   * @param string $id
   * @param string $status
   */
  public function __construct(
    #[Assert\NotBlank]
    private string $id,
    
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [UserStatus::class, 'values'])]
    private string $status
  ) {
  }
  
  /**
   * @return string
   */
  public function getId(): string {
    return $this->id;
  }
  
  /**
   * @return UserStatus
   */
  public function getStatus(): UserStatus {
    return UserStatus::from($this->status);
  }
}