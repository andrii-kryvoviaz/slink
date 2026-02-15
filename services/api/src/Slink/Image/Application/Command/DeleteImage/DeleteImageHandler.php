<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\DeleteImage;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class DeleteImageHandler implements CommandHandlerInterface {
  public function __construct(
    private ImageStoreRepositoryInterface $imageRepository,
  ) {
  }

  public function __invoke(
    DeleteImageCommand $command,
    string $userId,
    string $id
  ): void {
    $image = $this->imageRepository->get(ID::fromString($id));

    $image->delete(ID::fromString($userId), $command->preserveOnDisk());

    $this->imageRepository->store($image);
  }
}
