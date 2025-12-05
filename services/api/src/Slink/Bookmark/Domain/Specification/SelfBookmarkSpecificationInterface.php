<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\Specification;

use Slink\Shared\Domain\ValueObject\ID;

interface SelfBookmarkSpecificationInterface {
  public function ensureNotSelfBookmark(ID $imageId, ID $userId): void;
}
