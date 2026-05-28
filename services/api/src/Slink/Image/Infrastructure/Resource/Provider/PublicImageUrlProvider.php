<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Resource\Provider;

use Slink\Image\Application\Resource\ImageDataProviderInterface;
use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\PublicImageUrlBuilderInterface;
use Slink\Image\Infrastructure\Resource\ImageResourceContext;
use Slink\Shared\Application\Resource\ResourceContextInterface;
use Slink\Shared\Domain\Enum\ResourceProviderTag;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ResourceProviderTag::Image->value)]
final readonly class PublicImageUrlProvider implements ImageDataProviderInterface {
  public function __construct(
    private ImageRepositoryInterface $imageRepository,
    private PublicImageUrlBuilderInterface $urlBuilder,
  ) {
  }

  public function getProviderKey(): string {
    return 'urls';
  }

  public function supports(ResourceContextInterface $context): bool {
    if (!$context instanceof ImageResourceContext) {
      return false;
    }

    if (!$context->hasGroup('public')) {
      return false;
    }

    return $context->imageIds !== [];
  }

  /**
   * @param ImageResourceContext $context
   * @return array<string, string>
   */
  public function fetch(ResourceContextInterface $context): array {
    $imageIds = $context->imageIds;

    if (empty($imageIds)) {
      return [];
    }

    $images = $this->imageRepository->geImageList(new ImageListFilter(
      limit: count($imageIds),
      uuids: $imageIds,
    ));

    $result = [];

    foreach ($images as $image) {
      $result[$image->getUuid()] = $this->urlBuilder->build(
        $image->getUuid(),
        $image->getFileName(),
      );
    }

    return $result;
  }
}
