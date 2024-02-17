<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\RefreshToken;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RefreshTokenCommand implements CommandInterface {
  use EnvelopedMessage;
  
  public function __construct(
    #[Assert\NotBlank(message: 'The refresh token is required.')]
    private string $refresh_token,
  ) {}
  
  public function getRefreshToken(): string {
    return $this->refresh_token;
  }
}