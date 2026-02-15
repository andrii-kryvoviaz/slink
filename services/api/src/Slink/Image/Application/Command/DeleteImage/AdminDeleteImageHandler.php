<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\DeleteImage;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class AdminDeleteImageHandler implements CommandHandlerInterface {
  public function __construct(
    private ImageStoreRepositoryInterface $imageRepository,
  ) {
  }

  public function __invoke(
    AdminDeleteImageCommand $command,
    string $id
  ): void {
    $image = $this->imageRepository->get(ID::fromString($id));

    $image->forceDelete($command->preserveOnDisk());

    $this->imageRepository->store($image);
  }
}
