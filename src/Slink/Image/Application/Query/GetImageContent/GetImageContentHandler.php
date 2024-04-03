<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageContent;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final readonly class GetImageContentHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageStoreRepositoryInterface $store,
    private StorageInterface $storage
  ) {
  }
  
  /**
   * @throws NotFoundException
   */
  public function __invoke(GetImageContentQuery $query, string $id, string $ext): Item {
    $image = $this->store->get(ID::fromString($id));
    
    if(!$image->hasExtension($ext)) {
      throw new NotFoundException();
    }
    
    $imageOptions = ImageOptions::fromPayload([
      'fileName' => $image->getAttributes()->getFileName(),
      'mimeType' => $image->getMetadata()->getMimeType(),
      'width' => $query->getWidth(),
      'height' => $query->getHeight(),
      'quality' => $query->getQuality(),
      'crop' => $query->isCropped(),
    ]);
    
    $imageContent = $this->storage->getImage($imageOptions);
    
    if($imageContent === null) {
      throw new NotFoundException();
    }
    
    return Item::fromContent($imageContent, $image->getMetadata()->getMimeType());
  }
}