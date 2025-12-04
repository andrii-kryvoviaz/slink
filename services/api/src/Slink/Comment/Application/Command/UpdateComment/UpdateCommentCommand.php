<?php

declare(strict_types=1);

namespace Slink\Comment\Application\Command\UpdateComment;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateCommentCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 2000)]
    private string $content,
  ) {
  }

  public function getContent(): string {
    return $this->content;
  }
}
