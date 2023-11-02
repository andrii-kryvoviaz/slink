<?php

declare(strict_types=1);

namespace Slik\Image\Application\Query\GetImageContent;

use Slik\Shared\Application\Http\Item;
use Slik\Shared\Application\Query\QueryHandlerInterface;
use Slik\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final readonly class GetImageContentHandler implements QueryHandlerInterface {
  public function __construct(private StorageInterface $storage) {
  }
  
  public function __invoke(GetImageContentQuery $query): Item {
    $imageContent = $this->storage->getImageContent($query->getFileName());
    
    return Item::fromContent($imageContent);
  }
}