<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Resource;

use Slink\Shared\Application\Http\Item;

interface ResourceProcessorInterface {
  /**
   * @param array<object> $entities
   * @return iterable<Item>
   */
  public function many(array $entities, ResourceContextInterface $context): iterable;

  public function one(object $entity, ResourceContextInterface $context): Item;
}
