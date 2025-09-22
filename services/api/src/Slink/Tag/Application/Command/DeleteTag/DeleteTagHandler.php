<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\DeleteTag;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;

final readonly class DeleteTagHandler implements CommandHandlerInterface {
  public function __construct(
    private TagStoreRepositoryInterface $tagStore,
  ) {
  }

  public function __invoke(DeleteTagCommand $command, string $userId): void {
    $tagId = ID::fromString($command->getId());
    $userIdObject = ID::fromString($userId);

    $tag = $this->tagStore->get($tagId);

    if (!$tag->getUserId()->equals($userIdObject)) {
      throw new \InvalidArgumentException('You can only delete your own tags');
    }

    $tag->delete();
    $this->tagStore->store($tag);
  }
}