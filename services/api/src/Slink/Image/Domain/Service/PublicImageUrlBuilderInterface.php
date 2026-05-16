<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

interface PublicImageUrlBuilderInterface {
  public function build(string $imageId, string $fileName): string;
}
