<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Resource;

use Slink\Shared\Application\Resource\ResourceDataProviderInterface;
use Slink\Shared\Domain\Enum\ResourceProviderTag;
use Slink\Shared\Infrastructure\Resource\AbstractResourceProcessor;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class CollectionItemResourceProcessor extends AbstractResourceProcessor {
  /**
   * @param iterable<ResourceDataProviderInterface> $providers
   */
  public function __construct(
    #[AutowireIterator(ResourceProviderTag::CollectionItem->value)]
    protected iterable $providers,
  ) {
  }

  protected function resourceName(): string {
    return CollectionItemResource::class;
  }

  protected function getDataProviders(): iterable {
    return $this->providers;
  }
}
