<?php

declare(strict_types=1);

namespace Slink\Bookmark\Infrastructure\Resource;

use Slink\Bookmark\Infrastructure\ReadModel\View\BookmarkView;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Resource\ResourceContextInterface;
use Slink\Shared\Application\Resource\ResourceDataProviderInterface;
use Slink\Shared\Domain\Enum\ResourceProviderTag;
use Slink\Shared\Infrastructure\Resource\AbstractResourceProcessor;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class BookmarkResourceProcessor extends AbstractResourceProcessor {
  /**
   * @param iterable<ResourceDataProviderInterface> $providers
   */
  public function __construct(
    #[AutowireIterator(ResourceProviderTag::Bookmark->value)]
    protected iterable $providers,
  ) {
  }

  protected function resourceName(): string {
    return BookmarkResource::class;
  }

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

    if ($context instanceof BookmarkResourceContext) {
      $context = $context->withBookmarks(
        array_filter($entities, static fn(object $entity): bool => $entity instanceof BookmarkView),
      );
    }

    return parent::many($entities, $context);
  }
}
