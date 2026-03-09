<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchReassignImages;

use Slink\Image\Application\Command\BatchReassignImages\Operation\BatchReassignOperationInterface;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class BatchReassignImagesHandler implements CommandHandlerInterface {
  /**
   * @param iterable<BatchReassignOperationInterface> $operations
   */
  public function __construct(
    private ImageStoreRepositoryInterface $imageRepository,
    #[AutowireIterator('batch_reassign.operation')]
    private iterable $operations,
  ) {}

  /**
   * @return array{processed: array<string>, failed: array<array{id: string, reason: string}>}
   */
  public function __invoke(
    BatchReassignImagesCommand $command,
    string $userId,
  ): array {
    $userID = ID::fromString($userId);
    $processed = [];
    $failed = [];

    $allOperations = iterator_to_array($this->operations);

    foreach ($command->getImageIds() as $imageId) {
      try {
        $id = ID::fromString($imageId);
        $image = $this->imageRepository->get($id);

        if (!$image->isOwedBy($userID)) {
          throw new AccessDeniedException();
        }

        foreach ($allOperations as $operation) {
          if ($operation->supports($command, $imageId)) {
            $operation->apply($image, $command, $imageId, $userID);
          }
        }

        $this->imageRepository->store($image);

        $processed[] = $imageId;
      } catch (\Throwable $e) {
        $failed[] = ['id' => $imageId, 'reason' => $e->getMessage()];
      }
    }

    return [
      'processed' => $processed,
      'failed' => $failed,
    ];
  }
}
