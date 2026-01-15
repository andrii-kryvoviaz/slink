<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Slink\Image\Domain\Enum\ImageFormat;
use Symfony\Component\HttpFoundation\File\File;

interface ImageConversionResolverInterface {
  public function resolve(File $file): ?ImageFormat;
}
