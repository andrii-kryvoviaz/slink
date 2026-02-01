<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Resource;

use Slink\Collection\Infrastructure\ReadModel\View\CollectionItemView;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Resource\ResourceInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ResourceData;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

final class CollectionItemResource implements ResourceInterface {
  public function __construct(
    private readonly CollectionItemView $item,
    private readonly ResourceData $data = new ResourceData(),
  ) {
  }

  public function getType(): string {
    return CollectionItemView::class;
  }

  #[Groups(['public'])]
  public string $id {
    get => $this->item->getId();
  }

  #[Groups(['public'])]
  public string $itemId {
    get => $this->item->getItemId();
  }

  #[Groups(['public'])]
  public string $itemType {
    get => $this->item->getItemType()->value;
  }

  #[Groups(['public'])]
  public float $position {
    get => $this->item->getPosition();
  }

  #[Groups(['public'])]
  public DateTime $addedAt {
    get => $this->item->getAddedAt();
  }

  /** @var array<string, mixed>|null */
  #[Groups(['public'])]
  #[SerializedName('item')]
  public ?array $itemData {
    get {
      $item = $this->data->get('items', $this->item->getItemId());

      if ($item instanceof Item && is_array($item->resource)) {
        return $item->resource;
      }

      return is_array($item) ? $item : null;
    }
  }
}
