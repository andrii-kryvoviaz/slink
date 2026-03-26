<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Slink\Shared\Domain\ValueObject\ImageOptions;

interface ImageRetrievalInterface {
  public function getImage(ImageOptions $image): ?string;
}
