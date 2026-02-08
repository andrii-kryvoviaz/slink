<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\MoveTag;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class MoveTagCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\Uuid]
    private ?string $parentId = null,
  ) {
  }

  public function getParentId(): ?string {
    return $this->parentId ?: null;
  }
}
