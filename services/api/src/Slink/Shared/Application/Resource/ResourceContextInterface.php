<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Resource;

interface ResourceContextInterface {
  /**
   * @return array<string>
   */
  public function getGroups(): array;

  public function hasGroup(string $group): bool;

  public function hasAnyGroup(string ...$groups): bool;
}