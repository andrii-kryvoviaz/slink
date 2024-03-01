<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UpdateImage;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\DateTime;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

final readonly class UpdateImageHandler implements CommandHandlerInterface {
  
  public function __construct(
    private ImageStoreRepositoryInterface $imageRepository,
  ) {
  }
  
  /**
   * @throws NotFoundException
   */
  public function __invoke(UpdateImageCommand $command): void {
    $image = $this->imageRepository->get($command->getId());
    
    if (!$image->aggregateRootVersion()) {
      throw new NotFoundException();
    }
    
    $attributes = clone $image->getAttributes()
      ->withDescription($command->getDescription())
      ->withIsPublic($command->getIsPublic());
    
    $image->updateAttributes($attributes);
    
    $this->imageRepository->store($image);
  }
}