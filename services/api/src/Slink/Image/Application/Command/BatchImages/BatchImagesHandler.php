<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchImages;

use Slink\Image\Application\Command\BatchImages\Operation\BatchImageOperationInterface;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class BatchImagesHandler implements CommandHandlerInterface {
  /**
   * @param iterable<BatchImageOperationInterface> $operations
   */
  public function __construct(
    private ImageStoreRepositoryInterface $imageRepository,
    #[AutowireIterator('batch_image.operation')]
    private iterable $operations,
  ) {}

  /**
   * @return array{processed: array<string>, failed: array<array{id: string, reason: string}>}
   */
  public function __invoke(
    BatchImagesCommand $command,
    string $userId,
  ): array {
    $userID = ID::fromString($userId);
    $processed = [];
    $failed = [];

    $activeOperations = array_filter(
      iterator_to_array($this->operations),
      static fn (BatchImageOperationInterface $op) => $op->supports($command),
    );

    foreach ($command->imageIds() as $imageId) {
      try {
        $id = ID::fromString($imageId);
        $image = $this->imageRepository->get($id);

        if (!$image->isOwedBy($userID)) {
          throw new AccessDeniedException();
        }

        foreach ($activeOperations as $operation) {
          $operation->apply($image, $command, $userID);
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
