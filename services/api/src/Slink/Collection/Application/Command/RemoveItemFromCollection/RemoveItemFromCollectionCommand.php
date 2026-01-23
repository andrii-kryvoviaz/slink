<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\RemoveItemFromCollection;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RemoveItemFromCollectionCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $collectionId,

    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $itemId,
  ) {
  }

  public function getCollectionId(): string {
    return $this->collectionId;
  }

  public function getItemId(): string {
    return $this->itemId;
  }
}
