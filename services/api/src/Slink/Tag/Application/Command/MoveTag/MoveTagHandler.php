<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\MoveTag;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Exception\TagAccessDeniedException;
use Slink\Tag\Domain\Exception\TagMoveNotAllowedException;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Specification\TagDuplicateSpecificationInterface;
use Slink\Tag\Domain\ValueObject\TagPath;

final readonly class MoveTagHandler implements CommandHandlerInterface {
  public function __construct(
    private TagStoreRepositoryInterface        $tagStore,
    private TagRepositoryInterface             $tagRepository,
    private TagDuplicateSpecificationInterface $duplicateSpecification,
  ) {
  }

  public function __invoke(MoveTagCommand $command, string $userId, string $id): void {
    $userId = ID::fromString($userId);
    $tagId = ID::fromString($id);
    $parentId = $command->getParentId() ? ID::fromString($command->getParentId()) : null;

    $tag = $this->tagStore->get($tagId);

    if (!$tag->getUserId()->equals($userId)) {
      throw new TagAccessDeniedException();
    }

    if ($parentId && $parentId->equals($tagId)) {
      throw new TagMoveNotAllowedException('Tag cannot be its own parent.');
    }

    $currentPath = $tag->getPath()->getValue();

    $parentPath = null;
    if ($parentId) {
      $parentView = $this->tagRepository->oneById($parentId);
      if ($parentView->getUserId() !== $userId->toString()) {
        throw new TagAccessDeniedException();
      }

      $parentPath = TagPath::fromString($parentView->getPath());

      if ($parentPath->isDescendantOf($tag->getPath())) {
        throw new TagMoveNotAllowedException('Tag cannot be moved under its descendant.');
      }
    }

    $newPath = $parentPath
      ? TagPath::createChild($parentPath, $tag->getName())
      : TagPath::createRoot($tag->getName());

    $isSameParent = $tag->getParentId()?->equals($parentId) ?? ($parentId === null);
    if ($isSameParent && $currentPath === $newPath->getValue()) {
      return;
    }

    $this->duplicateSpecification->ensureUnique($tag->getName(), $userId, $parentId);

    $now = DateTime::now();
    $tag->move($parentId, $newPath, $now);
    $this->tagStore->store($tag);

    $descendants = $this->tagRepository->findDescendantsByPaths([$currentPath], $userId);
    if (empty($descendants)) {
      return;
    }

    foreach ($descendants as $descendant) {
      $descendantId = ID::fromString($descendant->getUuid());
      $descendantTag = $this->tagStore->get($descendantId);

      if (!$descendantTag->getUserId()->equals($userId)) {
        continue;
      }

      $descendantOldPath = $descendant->getPath();
      $suffix = substr($descendantOldPath, strlen($currentPath));
      $newDescendantPath = $newPath->getValue() . $suffix;

      $descendantTag->updatePath(TagPath::fromString($newDescendantPath), $now);
      $this->tagStore->store($descendantTag);
    }
  }
}
