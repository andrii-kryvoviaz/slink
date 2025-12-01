<?php

declare(strict_types=1);

namespace Slink\Comment\Application\Command\CreateComment;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateCommentCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 2000)]
    private string $content,

    #[Assert\Uuid]
    private ?string $referencedCommentId = null,
  ) {
  }

  public function getContent(): string {
    return $this->content;
  }

  public function getReferencedCommentId(): ?string {
    return $this->referencedCommentId;
  }
}
