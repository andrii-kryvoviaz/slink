<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Exception\Storage;

final class LocalStorageException extends \RuntimeException {
  public static function unableToCreateDirectory(string $path): self {
    return new self(\sprintf('Unable to create directory "%s".', $path));
  }
}
