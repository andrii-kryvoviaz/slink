<?php

declare(strict_types=1);

namespace Slik\Image\Application\Command\AddImageViewCount;

use Slik\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slik\Shared\Application\Command\CommandHandlerInterface;
use Slik\Shared\Domain\ValueObject\ID;

final readonly class AddImageViewCountHandler implements CommandHandlerInterface {
  
  public function __construct(private ImageStoreRepositoryInterface $imageRepository) {
  }
  
  public function __invoke(AddImageViewCountCommand $command): void {
    $image = $this->imageRepository->get(ID::fromString($command->getId()));
    
    $image->addView();
    
    $this->imageRepository->store($image);
  }
}