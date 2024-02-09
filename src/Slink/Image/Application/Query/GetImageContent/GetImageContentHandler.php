<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageContent;

use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final readonly class GetImageContentHandler implements QueryHandlerInterface {
  public function __construct(private StorageInterface $storage) {
  }
  
  /**
   * @throws NotFoundException
   */
  public function __invoke(GetImageContentQuery $query): Item {
    $imageContent = $this->storage->getImage($query->getImageOptions());
    
    return Item::fromContent($imageContent);
  }
}