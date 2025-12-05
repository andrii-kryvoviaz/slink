<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\Repository;

use Slink\Bookmark\Domain\Bookmark;
use Slink\Shared\Domain\ValueObject\ID;

interface BookmarkStoreRepositoryInterface {
  public function store(Bookmark $bookmark): void;

  public function get(ID $id): Bookmark;
}
