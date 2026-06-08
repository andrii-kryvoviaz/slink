<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ChunkedUpload;

final readonly class CompletionResult {
  public function __construct(
    public string $imageId,
    public bool $created,
  ) {
  }
}
