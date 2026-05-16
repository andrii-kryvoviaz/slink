<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\ValueObject;

use Slink\Image\Infrastructure\ReadModel\View\ImageView;

final readonly class CollectionScopedImageAccessContext {
  public function __construct(
    public string $collectionId,
    public string $itemId,
    public ImageView $imageView,
  ) {}
}
