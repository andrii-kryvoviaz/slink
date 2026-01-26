<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Resource;

use Slink\Shared\Infrastructure\Resource\ResourceContextInterface;

interface ResourceDataProviderInterface {
  public function getProviderKey(): string;

  public function supports(ResourceContextInterface $context): bool;

  /**
   * @return array<string, mixed>
   */
  public function fetch(ResourceContextInterface $context): array;
}
