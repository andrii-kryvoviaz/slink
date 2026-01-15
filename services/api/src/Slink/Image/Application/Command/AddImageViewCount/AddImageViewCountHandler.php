<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\AddImageViewCount;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class AddImageViewCountHandler implements CommandHandlerInterface {
  
  public function __construct(private ImageStoreRepositoryInterface $imageRepository) {
  }
  
  public function __invoke(AddImageViewCountCommand $command, ?string $userId = null): void {
    $imageId = ID::fromString($command->getId());
    $viewerId = $userId !== null ? ID::fromString($userId) : null;
    
    $image = $this->imageRepository->get($imageId);
    
    if ($viewerId?->equals($image->getUserId())) {
      return;
    }
    
    $image->addView();
    
    $this->imageRepository->store($image);
  }
}