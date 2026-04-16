<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Enum;

enum ImageAccess: string {
  case View = 'image.view';
  case Edit = 'image.edit';
  case Delete = 'image.delete';
  case Tag = 'image.tag';
}
