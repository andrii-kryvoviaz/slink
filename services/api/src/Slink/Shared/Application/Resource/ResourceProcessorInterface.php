<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Resource;

use Slink\Shared\Application\Http\Item;
use Slink\Shared\Infrastructure\Resource\ResourceContextInterface;

interface ResourceProcessorInterface {
  /**
   * @param array<object> $entities
   * @return array<Item>
   */
  public function many(array $entities, ResourceContextInterface $context): array;

  public function one(object $entity, ResourceContextInterface $context): Item;
}
