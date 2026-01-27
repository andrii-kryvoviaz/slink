<?php

declare(strict_types=1);

namespace Slink\Image\Application\Resource;

use Slink\Shared\Application\Resource\ResourceContextInterface;
use Slink\Shared\Application\Resource\ResourceDataProviderInterface;

interface ImageDataProviderInterface extends ResourceDataProviderInterface {
  public function supports(ResourceContextInterface $context): bool;

  /**
   * @return array<string, mixed>
   */
  public function fetch(ResourceContextInterface $context): array;
}
