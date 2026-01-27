<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Resource;

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
}
