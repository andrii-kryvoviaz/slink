<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Filter;

final readonly class TagFilterData {
  /**
   * @param array<string> $originalTagIds - The original tag IDs requested by the user
   * @param array<string> $expandedTagIds - All tag IDs including descendants for OR filtering
   * @param array<string, array<string>> $tagGroupMap - Maps each original tag ID to its descendants for AND filtering
   * @param bool $requireAllTags - Whether all original tags must be matched
   */
  public function __construct(
    private array $originalTagIds = [],
    private array $expandedTagIds = [],
    private array $tagGroupMap = [],
    private bool $requireAllTags = false,
  ) {}

  /**
   * @return array<string>
   */
  public function getOriginalTagIds(): array {
    return $this->originalTagIds;
  }

  /**
   * @return array<string>
   */
  public function getExpandedTagIds(): array {
    return $this->expandedTagIds;
  }

  /**
   * @return array<string, array<string>>
   */
  public function getTagGroupMap(): array {
    return $this->tagGroupMap;
  }

  public function requireAllTags(): bool {
    return $this->requireAllTags;
  }

  public function hasTagFilters(): bool {
    return !empty($this->originalTagIds);
  }
}