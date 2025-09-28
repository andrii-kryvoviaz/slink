<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\DeleteTag;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Exception\TagAccessDeniedException;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;

final readonly class DeleteTagHandler implements CommandHandlerInterface {
  public function __construct(
    private TagStoreRepositoryInterface $tagStore,
  ) {
  }

  public function __invoke(DeleteTagCommand $command, string $userId): void {
    $tagId = ID::fromString($command->getId());
    $userId = ID::fromString($userId);

    $tag = $this->tagStore->get($tagId);

    if (!$tag->getUserId()->equals($userId)) {
      throw new TagAccessDeniedException();
    }

    $tag->delete();
    $this->tagStore->store($tag);
  }
}