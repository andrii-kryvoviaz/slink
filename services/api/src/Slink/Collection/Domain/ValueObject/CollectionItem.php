<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\ValueObject;

use Slink\Collection\Domain\Enum\ItemType;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CollectionItem extends AbstractValueObject {
  private function __construct(
    private ID $itemId,
    private ItemType $itemType,
    private float $position,
    private DateTime $addedAt,
  ) {
  }

  public static function create(ID $itemId, ItemType $itemType, float $position, ?DateTime $addedAt = null): self {
    return new self(
      $itemId,
      $itemType,
      $position,
      $addedAt ?? DateTime::now(),
    );
  }

  public static function forImage(ID $imageId, float $position, ?DateTime $addedAt = null): self {
    return self::create($imageId, ItemType::Image, $position, $addedAt);
  }

  public function getItemId(): ID {
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

  public function withPosition(float $position): self {
    return new self($this->itemId, $this->itemType, $position, $this->addedAt);
  }

  public function toPayload(): array {
    return [
      'itemId' => $this->itemId->toString(),
      'itemType' => $this->itemType->value,
      'position' => $this->position,
      'addedAt' => $this->addedAt->toString(),
    ];
  }

  public static function fromPayload(array $payload): self {
    return new self(
      ID::fromString($payload['itemId']),
      ItemType::from($payload['itemType']),
      (float) $payload['position'],
      DateTime::fromString($payload['addedAt']),
    );
  }
}
