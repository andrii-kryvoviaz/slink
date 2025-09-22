<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Service;

use Slink\Image\Domain\Filter\TagFilterData;

interface TagFilterServiceInterface {
  /**
   * Create optimized TagFilterData with path-based descendant querying
   *
   * @param string[]|null $originalTagIds
   * @param bool $requireAllTags
   * @param string|null $userId
   * @return TagFilterData
   */
  public function createTagFilterData(array|null $originalTagIds, bool $requireAllTags = false, ?string $userId = null): TagFilterData;
}