<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\AddImageViewCount;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class AddImageViewCountHandler implements CommandHandlerInterface {
  
  public function __construct(private ImageStoreRepositoryInterface $imageRepository) {
  }
  
  public function __invoke(AddImageViewCountCommand $command): void {
    $image = $this->imageRepository->get(ID::fromString($command->getId()));
    
    $image->addView();
    
    $this->imageRepository->store($image);
  }
}