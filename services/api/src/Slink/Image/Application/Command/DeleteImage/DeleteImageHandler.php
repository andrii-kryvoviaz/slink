<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\DeleteImage;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class DeleteImageHandler implements CommandHandlerInterface {
  public function __construct(
    private ImageStoreRepositoryInterface $imageRepository,
    private StorageInterface $storage,
  ) {
  }
  
  public function __invoke(
    DeleteImageCommand $command,
    string $userId,
    string $id
  ): void {
    $image = $this->imageRepository->get(ID::fromString($id));
    $userID = ID::fromString($userId);
    
    if (!$image->aggregateRootVersion() || $image->isDeleted()) {
      throw new NotFoundException();
    }
    
    if (!$userId || !$image->getUserId() || !$image->getUserId()->equals($userID)) {
      throw new AccessDeniedException();
    }
    
    if (!$command->preserveOnDisk()) {
      $this->storage->delete($image->getAttributes()->getFileName());
    }
    
    $image->delete($command->preserveOnDisk());
    
    $this->imageRepository->store($image);
  }
}