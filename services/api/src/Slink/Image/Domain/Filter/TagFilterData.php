<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Filter;

final readonly class TagFilterData {
  /**
   * @param array<string> $originalTagIds - The original tag IDs requested by the user
   * @param bool $requireAllTags - Whether all original tags must be matched
   * @param array<string> $tagPaths - The materialized paths for efficient path-based filtering
   */
  public function __construct(
    private array $originalTagIds = [],
    private bool $requireAllTags = false,
    private array $tagPaths = [],
  ) {}

  /**
   * @return array<string>
   */
  public function getOriginalTagIds(): array {
    return $this->originalTagIds;
  }

  public function requireAllTags(): bool {
    return $this->requireAllTags;
  }

  /**
   * @return array<string>
   */
  public function getTagPaths(): array {
    return $this->tagPaths;
  }

  public function hasTagFilters(): bool {
    return !empty($this->originalTagIds);
  }
}