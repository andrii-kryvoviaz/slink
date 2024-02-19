<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\ChangeUserStatus;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\User\Domain\Enum\UserStatus;

final readonly class ChangeUserStatusCommand implements CommandInterface {
  
  /**
   * @param string $id
   * @param string $status
   */
  public function __construct(private string $id, private string $status) {
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