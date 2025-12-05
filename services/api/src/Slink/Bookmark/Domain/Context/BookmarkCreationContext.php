<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\Context;

use Slink\Bookmark\Domain\Specification\SelfBookmarkSpecificationInterface;

final readonly class BookmarkCreationContext {
  public function __construct(
    public SelfBookmarkSpecificationInterface $selfBookmarkSpecification,
  ) {
  }
}
