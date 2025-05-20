<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Enum;

enum ImageCropPosition: string {
  case CROP_TOP = 'top';
  case CROP_CENTER = 'center';
  case CROP_BOTTOM = 'bottom';
  case CROP_LEFT = 'left';
  case CROP_RIGHT = 'right';
  case CROP_TOP_CENTER = 'top-center';
}
