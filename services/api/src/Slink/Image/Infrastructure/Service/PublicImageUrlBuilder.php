<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service;

use Slink\Image\Domain\Service\PublicImageUrlBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(PublicImageUrlBuilderInterface::class)]
final readonly class PublicImageUrlBuilder implements PublicImageUrlBuilderInterface {
  public function build(string $imageId, string $fileName): string {
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);

    return "/api/image/public/{$imageId}.{$extension}";
  }
}
