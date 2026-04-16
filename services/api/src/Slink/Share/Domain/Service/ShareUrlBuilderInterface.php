<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Service;

use Slink\Share\Domain\ValueObject\TargetPath;

interface ShareUrlBuilderInterface {
  public function buildTargetPath(string $imageId, string $fileName, ?int $width, ?int $height, bool $crop, ?string $format = null, ?string $filter = null): TargetPath;
}
