<?php

declare(strict_types=1);

namespace Slink\Image\Application\Resource;

use Slink\Shared\Application\Resource\ResourceDataProviderInterface;
use Slink\Shared\Infrastructure\Resource\ResourceContextInterface;

interface ImageDataProviderInterface extends ResourceDataProviderInterface {
  public function supports(ResourceContextInterface $context): bool;

  /**
   * @return array<string, mixed>
   */
  public function fetch(ResourceContextInterface $context): array;
}
