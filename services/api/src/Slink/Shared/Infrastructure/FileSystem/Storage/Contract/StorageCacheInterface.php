<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Storage\Contract;

interface StorageCacheInterface {
  public function clearCache(): int;
  public function existsInCache(string $fileName): bool;
  public function writeToCache(string $fileName, string $content): void;
  public function readFromCache(string $fileName): ?string;
  public function deleteFromCache(string $fileName): void;
}
