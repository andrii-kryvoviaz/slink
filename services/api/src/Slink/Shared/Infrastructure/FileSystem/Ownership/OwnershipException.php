<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Ownership;

final class OwnershipException extends \RuntimeException {
  public static function ownerFailed(string $path, string $owner): self {
    return new self(\sprintf('lchown failed for %s (owner=%s)', $path, $owner));
  }

  public static function groupFailed(string $path, string $group): self {
    return new self(\sprintf('lchgrp failed for %s (group=%s)', $path, $group));
  }

  public static function symlinkRefused(string $path): self {
    return new self(\sprintf('chmod refused for symlink %s (expected a regular path)', $path));
  }

  public static function modeFailed(string $path, int $mode): self {
    return new self(\sprintf('chmod failed for %s (mode=0%o)', $path, $mode));
  }
}
