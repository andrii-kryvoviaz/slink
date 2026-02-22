<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SsoSignIn;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SsoSignInCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    private string $code,

    #[Assert\NotBlank]
    private string $state,
  ) {}

  public function getCode(): string {
    return $this->code;
  }

  public function getState(): string {
    return $this->state;
  }
}
