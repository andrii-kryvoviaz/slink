<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\AddImageViewCount;

use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class AddImageViewCountHandler implements CommandHandlerInterface {
  
  public function __construct(private ImageRepositoryInterface $imageRepository) {
  }
  
  public function __invoke(AddImageViewCountCommand $command, ?string $userId = null): void {
    $imageId = ID::fromString($command->getId());
    $viewerId = $userId !== null ? ID::fromString($userId) : null;

    $this->imageRepository->incrementViews($imageId, $viewerId);
  }
}
