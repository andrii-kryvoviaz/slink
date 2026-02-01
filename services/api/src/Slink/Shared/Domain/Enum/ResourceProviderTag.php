<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Enum;

enum ResourceProviderTag: string {
  case Image = 'image';
  case Bookmark = 'bookmark';
  case Collection = 'collection';
  case CollectionItem = 'collection_item';

  public function tag(): string {
    return sprintf('resource.data_provider.%s', $this->value);
  }
}
