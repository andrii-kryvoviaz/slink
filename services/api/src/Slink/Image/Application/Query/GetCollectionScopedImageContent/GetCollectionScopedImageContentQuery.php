<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetCollectionScopedImageContent;

use Slink\Collection\Domain\ValueObject\CollectionScopedImageAccessContext;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetCollectionScopedImageContentQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    public string $collectionId,
    public string $itemId,
  ) {
  }

  public function toAccessContext(ImageView $imageView): CollectionScopedImageAccessContext {
    return new CollectionScopedImageAccessContext($this->collectionId, $this->itemId, $imageView);
  }
}
