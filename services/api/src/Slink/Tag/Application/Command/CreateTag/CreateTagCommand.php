<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\CreateTag;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateTagCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 50)]
    #[Assert\Regex(
      pattern: '/^[a-zA-Z0-9_-]+$/',
      message: 'Tag name can only contain letters, numbers, hyphens, and underscores'
    )]
    private string $name,

    #[Assert\Uuid]
    private ?string $parentId = null,
  ) {}

  public function getName(): string {
    return $this->name;
  }

  public function getParentId(): ?string {
    return $this->parentId;
  }
}