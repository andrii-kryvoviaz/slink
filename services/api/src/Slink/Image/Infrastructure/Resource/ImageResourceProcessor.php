<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Resource;

use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Resource\ResourceContextInterface;
use Slink\Shared\Application\Resource\ResourceDataProviderInterface;
use Slink\Shared\Domain\Enum\ResourceProviderTag;
use Slink\Shared\Infrastructure\Resource\AbstractResourceProcessor;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class ImageResourceProcessor extends AbstractResourceProcessor {
  /**
   * @param iterable<ResourceDataProviderInterface> $providers
   */
  public function __construct(
    #[AutowireIterator(ResourceProviderTag::Image->value)]
    protected iterable $providers,
  ) {
  }

  /**
   * @return string
   */
  protected function resourceName(): string {
    return ImageResource::class;
  }

  /**
   * @return iterable<ResourceDataProviderInterface>
   */
  protected function getDataProviders(): iterable {
    return $this->providers;
  }

  /**
   * @param iterable<object> $entities
   * @param ResourceContextInterface $context
   * @return iterable<Item>
   */
  public function many(iterable $entities, ResourceContextInterface $context): iterable {
    if (!is_array($entities)) {
      $entities = iterator_to_array($entities, preserve_keys: false);
    }

    if ($context instanceof ImageResourceContext) {
      $imageIds = array_values(array_map(
        static fn(ImageView $view): string => $view->getUuid(),
        array_filter($entities, static fn(object $entity): bool => $entity instanceof ImageView),
      ));

      $context = $context->withImageIds($imageIds);
    }

    return parent::many($entities, $context);
  }
}
