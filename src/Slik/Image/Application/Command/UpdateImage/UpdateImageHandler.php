<?php

declare(strict_types=1);

namespace Slik\Image\Application\Command\UpdateImage;

use Slik\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slik\Image\Domain\ValueObject\ImageAttributes;
use Slik\Shared\Application\Command\CommandHandlerInterface;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\DateTime;

final readonly class UpdateImageHandler implements CommandHandlerInterface {
  
  public function __construct(
    private ImageStoreRepositoryInterface $imageRepository,
  ) {
  }
  
  public function __invoke(UpdateImageCommand $command): void {
    $image = $this->imageRepository->get($command->getId());
    
    $attributes = clone $image->getAttributes()
      ->withDescription($command->getDescription())
      ->withIsPublic($command->getIsPublic());
    
    $image->updateAttributes($attributes);
    
    $this->imageRepository->store($image);
  }
}