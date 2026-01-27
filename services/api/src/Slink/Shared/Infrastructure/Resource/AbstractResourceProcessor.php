<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Resource;

use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Resource\ResourceContextInterface;
use Slink\Shared\Application\Resource\ResourceDataProviderInterface;
use Slink\Shared\Application\Resource\ResourceInterface;
use Slink\Shared\Application\Resource\ResourceProcessorInterface;
use Slink\Shared\Domain\ValueObject\ResourceData;

abstract readonly class AbstractResourceProcessor implements ResourceProcessorInterface {
  /**
   * @retrun class-string<ResourceInterface>
   */
  abstract protected function resourceName(): string;

  /**
   * @return iterable<ResourceDataProviderInterface>
   */
  abstract protected function getDataProviders(): iterable;

  private function aggregate(ResourceContextInterface $context): ResourceData {
    $data = new ResourceData();

    foreach ($this->getDataProviders() as $provider) {
      if ($provider->supports($context)) {
        $data = $data->withProvider(
          $provider->getProviderKey(),
          $provider->fetch($context)
        );
      }
    }

    return $data;
  }

  /**
   * @param array<object> $entities
   * @param ResourceContextInterface $context
   * @return array<Item>
   */
  public function many(array $entities, ResourceContextInterface $context): array {
    $data = $this->aggregate($context);
    $className = $this->resourceName();

    return array_map(
      function (object $entity) use ($className, $data, $context): Item {
        /** @var ResourceInterface $resource */
        $resource = new $className($entity, $data);
        return Item::fromResource($resource, $context);
      },
      $entities
    );
  }

  /**
   * @param object $entity
   * @param ResourceContextInterface $context
   * @return Item
   */
  public function one(object $entity, ResourceContextInterface $context): Item {
    return $this->many([$entity], $context)[0];
  }
}