<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Enum;

enum ImageFeedEventType: string {
  case Added = 'image_added';
  case Updated = 'image_updated';
  case Removed = 'image_removed';
}
