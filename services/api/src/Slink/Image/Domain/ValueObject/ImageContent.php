<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

final readonly class ImageContent {
  public function __construct(
    public string $content,
    public string $mimeType,
  ) {
  }
}
