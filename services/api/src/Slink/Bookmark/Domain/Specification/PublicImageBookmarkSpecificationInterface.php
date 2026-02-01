<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\Specification;

use Slink\Shared\Domain\ValueObject\ID;

interface PublicImageBookmarkSpecificationInterface {
  public function ensureImageIsPublic(ID $imageId): void;
}
