<?php

declare(strict_types=1);

namespace Slik\Image\Application\Query;

use Doctrine\ORM\NonUniqueResultException;
use Slik\Image\Infrastructure\ReadModel\Repository\ImageRepository;
use Slik\Shared\Application\Http\Item;
use Slik\Shared\Application\Query\QueryHandlerInterface;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
use Slik\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final readonly class GetImageByIdHandler implements QueryHandlerInterface {
  public function __construct(private ImageRepository $repository, private StorageInterface $storage) {
  }
  
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function __invoke(GetImageByIdQuery $query): Item {
    $imageView = $this->repository->oneById($query->getId());
    
    return Item::fromPayload(ImageRepository::entityClass(), [
      'id' => $imageView->getUuid(),
      'url' => $this->storage->url($imageView->getAttributes()->getFileName(), 'images'),
      ...$imageView->getAttributes()->toPayload(),
      ...$imageView->getMetadata()? $imageView->getMetadata()->toPayload() : []
    ]);
  }
}