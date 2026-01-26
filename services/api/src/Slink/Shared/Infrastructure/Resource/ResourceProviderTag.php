<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Resource;

enum ResourceProviderTag: string {
  case Image = 'image';
  case Bookmark = 'bookmark';
  case Collection = 'collection';

  public function tag(): string {
    return sprintf('resource.data_provider.%s', $this->value);
  }
}
