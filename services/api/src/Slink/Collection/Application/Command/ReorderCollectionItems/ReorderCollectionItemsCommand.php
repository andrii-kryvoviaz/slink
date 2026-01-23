<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Command\ReorderCollectionItems;

use Slink\Shared\Application\Command\CommandInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ReorderCollectionItemsCommand implements CommandInterface {
  use EnvelopedMessage;

  /**
   * @param array<string> $orderedItemIds
   */
  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $collectionId,

    #[Assert\NotBlank]
    #[Assert\All([
      new Assert\Uuid()
    ])]
    private array $orderedItemIds,
  ) {
  }

  public function getCollectionId(): string {
    return $this->collectionId;
  }

  /**
   * @return array<string>
   */
  public function getOrderedItemIds(): array {
    return $this->orderedItemIds;
  }
}
