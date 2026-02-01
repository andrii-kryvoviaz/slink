<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Resource;

use Slink\Collection\Domain\Enum\ItemType;
use Slink\Shared\Application\Resource\ResourceDataProviderInterface;

interface ItemDataProviderInterface extends ResourceDataProviderInterface {
  public function supportsItemType(ItemType $type): bool;
}
