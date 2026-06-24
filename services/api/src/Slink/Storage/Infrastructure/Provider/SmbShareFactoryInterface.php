<?php

declare(strict_types=1);

namespace Slink\Storage\Infrastructure\Provider;

use Icewind\SMB\IShare;

interface SmbShareFactoryInterface {
  /**
   * @param array<string, mixed> $config
   */
  public function create(array $config): IShare;
}
