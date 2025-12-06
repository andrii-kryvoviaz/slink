<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

final readonly class ShareImageRequest {
  public function __construct(
    public ?int $width = null,
    public ?int $height = null,
    public bool $crop = false
  ) {
  }
}
