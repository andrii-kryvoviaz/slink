<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\LogOut;

use Slink\Shared\Application\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class LogOutCommand implements CommandInterface {
  
  public function __construct(
    #[Assert\NotBlank(message: 'The refresh token is required.')]
    private string $refresh_token,
  ) {}
  
  public function getRefreshToken(): string {
    return $this->refresh_token;
  }
}