<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Enum;

enum CollectionScopedImageAccess: string {
  case View = 'collection_scoped_image.view';
}
