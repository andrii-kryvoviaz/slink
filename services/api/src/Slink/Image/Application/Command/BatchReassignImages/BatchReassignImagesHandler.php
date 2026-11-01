<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchReassignImages;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Slink\Image\Application\Command\BatchReassignImages\Operation\BatchReassignOperationInterface;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\ForbiddenException;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class BatchReassignImagesHandler implements CommandHandlerInterface {
  /**
   * @param iterable<BatchReassignOperationInterface> $operations
   */
  public function __construct(
    private ImageStoreRepositoryInterface $imageRepository,
    private EntityManagerInterface $entityManager,
    private ManagerRegistry $managerRegistry,
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

        if (!$image->isOwnedBy($userID)) {
          throw new ForbiddenException();
        }

        foreach ($allOperations as $operation) {
          if ($operation->supports($command, $imageId)) {
            $operation->apply($image, $command, $imageId, $userID);
          }
        }

        $this->imageRepository->store($image);
        $this->entityManager->flush();

        $processed[] = $imageId;
      } catch (OptimisticLockException $e) {
        throw $e;
      } catch (\Throwable $e) {
        $failed[] = ['id' => $imageId, 'reason' => $e->getMessage()];

        $this->recoverEntityManager();
      }
    }

    return [
      'processed' => $processed,
      'failed' => $failed,
    ];
  }

  private function recoverEntityManager(): void {
    if ($this->entityManager->isOpen()) {
      return;
    }

    $this->managerRegistry->resetManager();
  }
}
