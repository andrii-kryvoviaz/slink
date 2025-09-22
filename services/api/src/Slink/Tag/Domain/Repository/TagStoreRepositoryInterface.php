<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Tag;

interface TagStoreRepositoryInterface {
  public function get(ID $id): Tag;

  public function store(Tag $tag): void;
}