<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\MoveTag;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class MoveTagCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $id,

    #[Assert\Uuid]
    private ?string $newParentId = null,
  ) {}

  public function getId(): string {
    return $this->id;
  }

  public function getNewParentId(): ?string {
    return $this->newParentId;
  }
}
