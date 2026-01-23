<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\AddItemToCollection;

use Slink\Collection\Domain\Enum\ItemType;
use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class AddItemToCollectionCommand implements CommandInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $collectionId,
    private string $itemId,
    private ItemType $itemType = ItemType::Image,
  ) {
  }

  public function getCollectionId(): string {
    return $this->collectionId;
  }

  public function getItemId(): string {
    return $this->itemId;
  }

  public function getItemType(): ItemType {
    return $this->itemType;
  }
}
