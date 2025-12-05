<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Service;

interface ShareUrlBuilderInterface {
  public function buildTargetUrl(string $imageId, string $fileName, ?int $width, ?int $height, bool $crop): string;
}
