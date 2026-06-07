<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem;

final class FileStream {
  /**
   * @param resource $resource
   */
  public function __construct(
    private readonly mixed $resource
  ) {
  }

  /**
   * @return resource
   */
  public function resource(): mixed {
    return $this->resource;
  }

  public function __destruct() {
    if (\is_resource($this->resource)) {
      \fclose($this->resource);
    }
  }
}
