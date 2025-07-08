<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\DeleteImage;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

final readonly class AdminDeleteImageHandler implements CommandHandlerInterface {
  public function __construct(
    private ImageStoreRepositoryInterface $imageRepository,
    private StorageInterface $storage,
  ) {
  }
  
  public function __invoke(
    AdminDeleteImageCommand $command,
    string $id
  ): void {
    $image = $this->imageRepository->get(ID::fromString($id));
    
    if (!$image->aggregateRootVersion() || $image->isDeleted()) {
      throw new NotFoundException();
    }
    
    if (!$command->preserveOnDisk()) {
      $this->storage->delete($image->getAttributes()->getFileName());
    }
    
    $image->delete($command->preserveOnDisk());
    
    $this->imageRepository->store($image);
  }
}
