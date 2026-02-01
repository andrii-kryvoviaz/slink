<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\ReadModel\View;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Infrastructure\ReadModel\Repository\CollectionItemRepository;
use Slink\Shared\Domain\Contract\CursorAwareInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractView;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: '`collection_item`')]
#[ORM\Entity(repositoryClass: CollectionItemRepository::class)]
#[ORM\Index(columns: ['collection_id', 'position'], name: 'idx_collection_item_position')]
#[ORM\UniqueConstraint(name: 'unique_collection_item', columns: ['collection_id', 'item_id'])]
class CollectionItemView extends AbstractView implements CursorAwareInterface {
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[Groups(['public'])]
    #[SerializedName('id')]
    private string $uuid,

    #[ORM\ManyToOne(targetEntity: CollectionView::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'collection_id', referencedColumnName: 'uuid', nullable: false, onDelete: 'CASCADE')]
    private CollectionView $collection,

    #[ORM\Column(type: 'uuid')]
    #[Groups(['public'])]
    private string $itemId,

    #[ORM\Column(type: 'string', length: 32, enumType: ItemType::class)]
    #[Groups(['public'])]
    private ItemType $itemType,

    #[ORM\Column(type: 'float')]
    #[Groups(['public'])]
    private float $position,

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['public'])]
    private DateTime $addedAt,
  ) {
  }

  public function getId(): string {
    return $this->uuid;
  }

  public function getCollection(): CollectionView {
    return $this->collection;
  }

  public function getCollectionId(): string {
    return $this->collection->getId();
  }

  public function getItemId(): string {
    return $this->itemId;
  }

  public function getItemType(): ItemType {
    return $this->itemType;
  }

  public function getPosition(): float {
    return $this->position;
  }

  public function getAddedAt(): DateTime {
    return $this->addedAt;
  }

  public function setPosition(float $position): void {
    $this->position = $position;
  }

  public function getCursorId(): string {
    return $this->uuid;
  }

  public function getCursorTimestamp(): DateTimeInterface {
    return $this->addedAt->toDateTimeImmutable();
  }
}
