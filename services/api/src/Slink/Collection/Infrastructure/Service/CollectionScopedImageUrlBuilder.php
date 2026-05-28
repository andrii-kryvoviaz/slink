<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Service;

use Slink\Collection\Domain\Service\CollectionScopedImageUrlBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(CollectionScopedImageUrlBuilderInterface::class)]
final readonly class CollectionScopedImageUrlBuilder implements CollectionScopedImageUrlBuilderInterface {
  public function build(string $imageId, string $fileName, string $collectionId): string {
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);

    return "/image/collection/{$collectionId}/items/{$imageId}.{$extension}";
  }
}
