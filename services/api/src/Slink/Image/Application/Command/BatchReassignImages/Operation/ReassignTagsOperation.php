<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchReassignImages\Operation;

use Slink\Image\Application\Command\BatchReassignImages\BatchReassignImagesCommand;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\ValueObject\TagSet;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Enum\TagAccess;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AutoconfigureTag('batch_reassign.operation')]
final class ReassignTagsOperation implements BatchReassignOperationInterface {
  public function __construct(
    private readonly TagStoreRepositoryInterface $tagRepository,
    private readonly AuthorizationCheckerInterface $access,
  ) {}

  public function supports(BatchReassignImagesCommand $command, string $imageId): bool {
    return $command->getTagIdsForImage($imageId) !== null;
  }

  public function apply(Image $image, BatchReassignImagesCommand $command, string $imageId, ID $userId): void {
    $tagIds = $command->getTagIdsForImage($imageId);
    if ($tagIds === null) {
      return;
    }

    $validTagIds = array_reduce($tagIds, function (array $carry, string $tagId): array {
      try {
        $tag = $this->tagRepository->get(ID::fromString($tagId));
        if ($this->access->isGranted(TagAccess::Use, $tag)) {
          $carry[] = ID::fromString($tagId);
        }
      } catch (\Throwable) {
      }

      return $carry;
    }, []);

    $newTags = TagSet::create($validTagIds);
    $image->reassignTags($newTags, $userId);
  }
}
