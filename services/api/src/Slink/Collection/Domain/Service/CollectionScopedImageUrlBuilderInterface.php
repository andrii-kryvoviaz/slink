<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Service;

interface CollectionScopedImageUrlBuilderInterface {
  public function build(string $imageId, string $fileName, string $collectionId): string;
}
