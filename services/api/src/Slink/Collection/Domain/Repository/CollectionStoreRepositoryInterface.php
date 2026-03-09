<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Repository;

use Slink\Collection\Domain\Collection;
use Slink\Shared\Domain\DataStructures\HashMap;
use Slink\Shared\Domain\ValueObject\ID;

interface CollectionStoreRepositoryInterface {
  public function store(Collection $collection): void;

  public function get(ID $id): Collection;

  /** @param array<string> $ids */
  public function getByIds(array $ids): HashMap;
}
