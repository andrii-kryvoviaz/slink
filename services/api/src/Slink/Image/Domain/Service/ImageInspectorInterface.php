<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Slink\Image\Domain\ValueObject\AnimatedImageInfo;

interface ImageInspectorInterface {
  public function getAnimatedImageInfo(string $content): AnimatedImageInfo;
}
