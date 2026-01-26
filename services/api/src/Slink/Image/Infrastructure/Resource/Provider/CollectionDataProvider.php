<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Resource\Provider;

use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Image\Application\Resource\ImageDataProviderInterface;
use Slink\Image\Infrastructure\Resource\ImageResourceContext;
use Slink\Shared\Infrastructure\Resource\ResourceContextInterface;
use Slink\Shared\Infrastructure\Resource\ResourceProviderTag;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ResourceProviderTag::Image->value)]
final readonly class CollectionDataProvider implements ImageDataProviderInterface {
  public function __construct(
    private CollectionItemRepositoryInterface $repository,
  ) {
  }

  public function getProviderKey(): string {
    return 'collections';
  }

  public function supports(ResourceContextInterface $context): bool {
    return $context->hasGroup('collection');
  }

  /**
   * @param ImageResourceContext $context
   * @return array<string, array<string>>
   */
  public function fetch(ResourceContextInterface $context): array {
    return $this->repository->getCollectionIdsByImageIds($context->imageIds);
  }
}
