<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Repository;

use Slink\Collection\Domain\Collection;
use Slink\Shared\Domain\ValueObject\ID;

interface CollectionStoreRepositoryInterface {
  public function store(Collection $collection): void;

  public function get(ID $id): Collection;
}
