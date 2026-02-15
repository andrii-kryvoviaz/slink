<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchDeleteImages;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class BatchDeleteImagesHandler implements CommandHandlerInterface {
  public function __construct(
    private ImageStoreRepositoryInterface $imageRepository,
  ) {}

  public function __invoke(
    BatchDeleteImagesCommand $command,
    string $userId,
  ): BatchDeleteImagesResult {
    $userID = ID::fromString($userId);
    $deleted = [];
    $failed = [];

    foreach ($command->imageIds() as $imageId) {
      try {
        $id = ID::fromString($imageId);
        $image = $this->imageRepository->get($id);

        $image->delete($userID, $command->preserveOnDisk());
        $this->imageRepository->store($image);

        $deleted[] = $imageId;
      } catch (\Throwable $e) {
        $failed[] = ['id' => $imageId, 'reason' => $e->getMessage()];
      }
    }

    return new BatchDeleteImagesResult($deleted, $failed);
  }
}
