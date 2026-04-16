<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Enum;

enum CollectionAccess: string {
  case View = 'collection.view';
  case Edit = 'collection.edit';
  case Delete = 'collection.delete';
  case ManageItems = 'collection.manage_items';
}
