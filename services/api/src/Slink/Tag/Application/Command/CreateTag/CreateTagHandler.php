<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Command\CreateTag;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Exception\DuplicateTagException;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Specification\TagDuplicateSpecificationInterface;
use Slink\Tag\Domain\Tag;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;

final readonly class CreateTagHandler implements CommandHandlerInterface {
  public function __construct(
    private TagStoreRepositoryInterface        $tagStore,
    private TagDuplicateSpecificationInterface $duplicateSpecification,
  ) {
  }

  /**
   * @throws DuplicateTagException
   */
  public function __invoke(CreateTagCommand $command, string $userId): ID {
    $userId = ID::fromString($userId);
    $tagName = TagName::fromString($command->getName());
    $parentId = ID::fromUnknown($command->getParentId());

    $this->duplicateSpecification->ensureUnique($tagName, $userId, $parentId);

    $parentPath = $this->resolveParentPath($parentId);

    $tagId = ID::generate();
    $tag = Tag::create($tagId, $userId, $tagName, $parentId, $parentPath);

    $this->tagStore->store($tag);

    return $tagId;
  }

  private function resolveParentPath(?ID $parentId): ?TagPath {
    return $parentId ? $this->tagStore->get($parentId)->getPath() : null;
  }
}
