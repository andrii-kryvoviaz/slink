<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Resource;

use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionItemView;
use Slink\Shared\Infrastructure\Resource\AbstractResourceContext;

final readonly class CollectionItemResourceContext extends AbstractResourceContext {
  /**
   * @param array<string> $groups
   * @param array<string> $itemIds
   * @param array<string, array<string>> $itemIdsByType
   */
  public function __construct(
    array $groups = ['public'],
    public array $itemIds = [],
    public array $itemIdsByType = [],
    public ?string $viewerUserId = null,
  ) {
    parent::__construct($groups);
  }

  /**
   * @param iterable<CollectionItemView> $items
   */
  public function withItems(iterable $items): self {
    $itemIds = [];
    $itemIdsByType = [];

    foreach ($items as $item) {
      $itemId = $item->getItemId();
      $type = $item->getItemType()->value;

      $itemIds[] = $itemId;
      $itemIdsByType[$type] ??= [];
      $itemIdsByType[$type][] = $itemId;
    }

    return new self(
      $this->getGroups(),
      $itemIds,
      $itemIdsByType,
      $this->viewerUserId,
    );
  }

  /**
   * @return array<string>
   */
  public function getItemIdsByType(ItemType $type): array {
    return $this->itemIdsByType[$type->value] ?? [];
  }

  public function hasItemsOfType(ItemType $type): bool {
    return !empty($this->itemIdsByType[$type->value]);
  }
}
