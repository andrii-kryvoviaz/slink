<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\DeleteOAuthProvider;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class DeleteOAuthProviderCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    private string $id,
  ) {}

  public function getId(): string {
    return $this->id;
  }
}
