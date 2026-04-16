<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\DeleteTag;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Enum\TagAccess;
use Slink\Tag\Domain\Exception\TagAccessDeniedException;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class DeleteTagHandler implements CommandHandlerInterface {
  public function __construct(
    private TagStoreRepositoryInterface $tagStore,
    private TagRepositoryInterface $tagRepository,
    private AuthorizationCheckerInterface $access,
  ) {
  }

  public function __invoke(DeleteTagCommand $command, string $userId): void {
    $tagId = ID::fromString($command->getId());

    $tag = $this->tagStore->get($tagId);

    if (!$this->access->isGranted(TagAccess::Delete, $tag)) {
      throw new TagAccessDeniedException();
    }

    $childIds = $this->tagRepository->findChildIds($tagId, $tag->getUserId());

    $tag->delete($childIds);
    $this->tagStore->store($tag);
  }
}