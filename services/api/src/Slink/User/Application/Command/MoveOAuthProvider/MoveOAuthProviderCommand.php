<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\MoveOAuthProvider;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Domain\Enum\SortDirection;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class MoveOAuthProviderCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    private string $id,
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [SortDirection::class, 'values'])]
    private string $direction,
  ) {}

  public function getId(): string {
    return $this->id;
  }

  public function getDirection(): string {
    return $this->direction;
  }
}
