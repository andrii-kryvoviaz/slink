<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchImages\Operation;

use Slink\Image\Application\Command\BatchImages\BatchImagesCommand;
use Slink\Image\Domain\Image;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('batch_image.operation')]
final readonly class AssignTagsOperation implements BatchImageOperationInterface {
  public function __construct(
    private TagStoreRepositoryInterface $tagRepository,
  ) {}

  public function supports(BatchImagesCommand $command): bool {
    return $command->tagIds() !== null;
  }

  public function apply(Image $image, BatchImagesCommand $command, ID $userId): void {
    $tagIds = $command->tagIds();
    if ($tagIds === null) {
      return;
    }
    foreach ($tagIds as $tagId) {
      $tagID = ID::fromString($tagId);
      $tag = $this->tagRepository->get($tagID);

      if (!$tag->getUserId()->equals($userId)) {
        continue;
      }

      $image->tagWith($tagID, $userId);
    }
  }

}
