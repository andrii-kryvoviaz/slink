<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Storage\Contract;

interface DirectoryStorageInterface {
  /**
   * @param string $path
   * @return void
   */
  public function mkdir(string $path): void;
}