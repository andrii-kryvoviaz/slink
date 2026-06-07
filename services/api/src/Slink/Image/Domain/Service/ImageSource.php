<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

interface ImageSource {
  public function hasLocalPath(): bool;

  public function getLocalPath(): string;

  public function readBytes(): string;
}
