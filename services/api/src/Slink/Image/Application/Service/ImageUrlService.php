<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ImageUrlService {
  public function generateImageUrl(string $fileName): string {
    return "image/{$fileName}";
  }

  public function generateThumbnailUrl(string $fileName, int $width = 300, int $height = 300): string {
    return "image/{$fileName}?width={$width}&height={$height}&crop=true";
  }
}
