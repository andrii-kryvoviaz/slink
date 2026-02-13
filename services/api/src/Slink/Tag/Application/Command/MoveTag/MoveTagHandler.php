<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\MoveTag;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Exception\InvalidTagMoveException;
use Slink\Tag\Domain\Exception\TagAccessDeniedException;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Specification\TagCircularMoveSpecificationInterface;
use Slink\Tag\Domain\Specification\TagDuplicateSpecificationInterface;

final readonly class MoveTagHandler implements CommandHandlerInterface {
  public function __construct(
    private TagStoreRepositoryInterface              $tagStore,
    private TagDuplicateSpecificationInterface        $duplicateSpecification,
    private TagCircularMoveSpecificationInterface     $circularMoveSpecification,
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
      $this->circularMoveSpecification->validate($tagId, $newParentId);
    }

    $this->duplicateSpecification->ensureUnique($tag->getName(), $userId, $newParentId);

    $tag->move($newParentId);
    $this->tagStore->store($tag);
  }
}
