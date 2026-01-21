<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\DeleteCollection;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class DeleteCollectionCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $id,
    private bool $deleteImages = false,
  ) {
  }

  public function getId(): string {
    return $this->id;
  }

  public function shouldDeleteImages(): bool {
    return filter_var($this->deleteImages, FILTER_VALIDATE_BOOLEAN);
  }
}
