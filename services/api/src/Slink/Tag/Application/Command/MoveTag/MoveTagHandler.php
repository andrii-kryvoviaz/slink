<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\MoveTag;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Exception\InvalidTagMoveException;
use Slink\Tag\Domain\Exception\TagAccessDeniedException;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Specification\TagDuplicateSpecificationInterface;
use Slink\Tag\Domain\ValueObject\TagPath;

final readonly class MoveTagHandler implements CommandHandlerInterface {
  public function __construct(
    private TagStoreRepositoryInterface       $tagStore,
    private TagDuplicateSpecificationInterface $duplicateSpecification,
  ) {
  }

  public function __invoke(MoveTagCommand $command, string $userId): void {
    $tagId = ID::fromString($command->getId());
    $userId = ID::fromString($userId);
    $newParentId = ID::fromUnknown($command->getNewParentId());

    $tag = $this->tagStore->get($tagId);

    if (!$tag->getUserId()->equals($userId)) {
      throw new TagAccessDeniedException();
    }

    if ($newParentId !== null && $newParentId->equals($tagId)) {
      throw new InvalidTagMoveException('Cannot move a tag to itself');
    }

    if ($newParentId !== null) {
      $newParent = $this->tagStore->get($newParentId);

      if ($tag->getPath()->isParentOf($newParent->getPath())) {
        throw new InvalidTagMoveException('Cannot move a tag to one of its descendants');
      }

      $newPath = TagPath::createChild($newParent->getPath(), $tag->getName());
    } else {
      $newPath = TagPath::createRoot($tag->getName());
    }

    $this->duplicateSpecification->ensureUnique($tag->getName(), $userId, $newParentId);

    $tag->move($newParentId, $newPath);
    $this->tagStore->store($tag);
  }
}
