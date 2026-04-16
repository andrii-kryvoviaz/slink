<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Enum;

enum TagAccess: string {
  case View = 'tag.view';
  case Edit = 'tag.edit';
  case Delete = 'tag.delete';
  case Use = 'tag.use';
}
