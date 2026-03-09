<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Resource\Provider;

use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Image\Application\Resource\ImageDataProviderInterface;
use Slink\Image\Infrastructure\Resource\ImageResourceContext;
use Slink\Shared\Application\Resource\ResourceContextInterface;
use Slink\Shared\Domain\Enum\ResourceProviderTag;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AutoconfigureTag(ResourceProviderTag::Image->value)]
final readonly class CollectionDataProvider implements ImageDataProviderInterface {
  public function __construct(
    private CollectionItemRepositoryInterface $repository,
    private NormalizerInterface $normalizer,
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
   * @return array<string, array<array{id: string, name: string}>>
   */
  public function fetch(ResourceContextInterface $context): array {
    return array_map( // @phpstan-ignore return.type
      fn(array $collections) => array_map(
        fn(CollectionView $c) => $this->normalizer->normalize($c, context: ['groups' => ['reference']]),
        $collections
      ),
      $this->repository->getCollectionsByImageIds($context->imageIds)
    );
  }
}
