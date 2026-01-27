<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Resource;

use Slink\Shared\Application\Resource\ResourceContextInterface;

readonly abstract class AbstractResourceContext implements ResourceContextInterface {
  /**
   * @param array<string> $groups
   */
  public function __construct(
    private array $groups = ['public'],
  ) {
  }

  /**
   * @return array<string>
   */
  public function getGroups(): array {
    return $this->groups;
  }

  public function hasGroup(string $group): bool {
    return in_array($group, $this->groups, true);
  }

  public function hasAnyGroup(string ...$groups): bool {
    return !empty(array_intersect($this->groups, $groups));
  }
}
