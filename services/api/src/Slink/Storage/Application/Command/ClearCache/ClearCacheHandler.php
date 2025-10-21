<?php

declare(strict_types=1);

namespace Slink\Storage\Application\Command\ClearCache;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

final readonly class ClearCacheHandler implements CommandHandlerInterface {
  public function __construct(
    private StorageInterface $storage
  ) {
  }

  public function __invoke(ClearCacheCommand $command): int {
    return $this->storage->clearCache();
  }
}
