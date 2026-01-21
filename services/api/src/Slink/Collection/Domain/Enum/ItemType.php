<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Enum;

enum ItemType: string {
  case Image = 'image';
  case Video = 'video';
}
