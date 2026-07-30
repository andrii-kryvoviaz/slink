<?php

declare(strict_types=1);

namespace Slink\Settings\Application\Query\GetBrandingLogo;

use Slink\Settings\Domain\Enum\BrandingLogoFormat;
use Slink\Shared\Application\Http\CachePolicy;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\Exception\NotFoundException as DomainNotFoundException;
use Slink\Shared\Domain\FileSystem\Storage\StorageInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

final readonly class GetBrandingLogoHandler implements QueryHandlerInterface {
  public function __construct(
    private StorageInterface $storage,
  ) {
  }

  public function __invoke(GetBrandingLogoQuery $query): Item {
    foreach (BrandingLogoFormat::cases() as $format) {
      $content = $this->readContent($format);

      if ($content === null) {
        continue;
      }

      return Item::fromContent($content, $format->mimeType(), $this->cachePolicy($query));
    }

    throw new NotFoundException();
  }

  private function readContent(BrandingLogoFormat $format): ?string {
    try {
      return $this->storage->readImage($format->fileName());
    } catch (DomainNotFoundException) {
      return null;
    }
  }

  private function cachePolicy(GetBrandingLogoQuery $query): CachePolicy {
    if ($query->versioned) {
      return CachePolicy::publicImmutable();
    }

    return CachePolicy::revocable();
  }
}
