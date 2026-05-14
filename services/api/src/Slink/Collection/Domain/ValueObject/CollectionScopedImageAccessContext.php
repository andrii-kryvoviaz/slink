<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\ValueObject;

final readonly class CollectionScopedImageAccessContext {
  public function __construct(
    public string $collectionId,
    public string $itemId,
  ) {}
}
