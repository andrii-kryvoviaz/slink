<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Service;

use Slink\Collection\Domain\Service\CollectionScopedImageUrlBuilderInterface;
use Slink\Image\Domain\Service\ImageUrlSignatureInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(CollectionScopedImageUrlBuilderInterface::class)]
final readonly class CollectionScopedImageUrlBuilder implements CollectionScopedImageUrlBuilderInterface {
  public function __construct(
    private ImageUrlSignatureInterface $signatureService,
  ) {
  }

  public function build(string $imageId, string $fileName, string $collectionId): string {
    $signature = $this->signatureService->sign($imageId, ['collection' => $collectionId]);

    $queryParams = [
      'collection' => $collectionId,
      'cs' => $signature,
    ];

    return "/image/{$fileName}?" . http_build_query($queryParams);
  }
}
