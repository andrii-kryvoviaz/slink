<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

use Slink\Image\Infrastructure\ReadModel\View\ImageView;

final readonly class ImageAccessContext {
  public function __construct(
    public ImageView $image,
    public ?string $scopeCollectionId = null,
    public ?string $scopeSignature = null,
  ) {}
}
