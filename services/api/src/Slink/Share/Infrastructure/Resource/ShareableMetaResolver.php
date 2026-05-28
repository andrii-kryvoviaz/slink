<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Resource;

use Closure;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Service\ShareableMetaProviderInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class ShareableMetaResolver {
  /**
   * @param iterable<ShareableMetaProviderInterface> $providers
   */
  public function __construct(
    #[AutowireIterator(ShareableMetaProviderInterface::class)]
    private iterable $providers,
  ) {}

  /**
   * @param list<ShareView> $shares
   * @return Closure(string, ShareableType): array{id: string, name: string, previewUrl: ?string, width?: int, height?: int, format?: string}
   */
  public function resolver(array $shares): Closure {
    /** @var array<string, array<string, bool>> $idsByType */
    $idsByType = [];
    foreach ($shares as $share) {
      $ref = $share->getShareable();
      $idsByType[$ref->getShareableType()->value][$ref->getShareableId()] = true;
    }

    /** @var array<string, array<string, array{id: string, name: string, previewUrl: ?string, width?: int, height?: int, format?: string}>> $metaByType */
    $metaByType = [];
    foreach ($this->providers as $provider) {
      $type = $provider->supports();
      $ids = array_keys($idsByType[$type->value] ?? []);
      $metaByType[$type->value] = $provider->resolve($ids);
    }

    return static fn(string $id, ShareableType $type): array =>
      $metaByType[$type->value][$id] ?? ['id' => $id, 'name' => $id, 'previewUrl' => null];
  }
}
