<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Specification;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Exception\DuplicateTagException;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;

interface TagDuplicateSpecificationInterface {
  /**
   * @throws DuplicateTagException if duplicate found
   */
  public function ensureUnique(TagName $name, ID $userId, ?ID $parentId = null): void;

  /**
   * @throws DuplicateTagException if duplicate path found
   */
  public function ensurePathUnique(TagPath $path, ID $userId): void;
}
