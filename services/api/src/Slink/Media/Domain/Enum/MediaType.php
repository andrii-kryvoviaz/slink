<?php

declare(strict_types=1);

namespace Slink\Media\Domain\Enum;

enum MediaType: string {
  case Image = 'image';
  case Video = 'video';
}
