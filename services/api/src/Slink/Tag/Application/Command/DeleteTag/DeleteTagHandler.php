<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\DeleteTag;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Exception\TagAccessDeniedException;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\ValueObject\TagPath;

final readonly class DeleteTagHandler implements CommandHandlerInterface {
  public function __construct(
    private TagStoreRepositoryInterface $tagStore,
    private TagRepositoryInterface $tagRepository,
  ) {
  }

  public function __invoke(DeleteTagCommand $command, string $userId): void {
    $tagId = ID::fromString($command->getId());
    $userId = ID::fromString($userId);

    $tag = $this->tagStore->get($tagId);

    if (!$tag->getUserId()->equals($userId)) {
      throw new TagAccessDeniedException();
    }

    $tagsToDelete = $this->tagRepository->findTagsWithDescendants(
      [$tagId->toString()],
      $userId,
    );

    usort(
      $tagsToDelete,
      fn($a, $b) =>
        TagPath::fromString($b->getPath())->getDepth()
        <=> TagPath::fromString($a->getPath())->getDepth(),
    );

    foreach ($tagsToDelete as $tagView) {
      $tagAggregate = $this->tagStore->get(ID::fromString($tagView->getUuid()));
      if (!$tagAggregate->getUserId()->equals($userId)) {
        throw new TagAccessDeniedException();
      }

      $tagAggregate->delete();
      $this->tagStore->store($tagAggregate);
    }
  }
}
