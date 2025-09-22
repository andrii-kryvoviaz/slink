<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Specification;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Exception\DuplicateTagException;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;

final readonly class TagDuplicateSpecification implements TagDuplicateSpecificationInterface {
  public function __construct(private TagRepositoryInterface $tagRepository) {}

  public function ensureUnique(TagName $name, ID $userId, ?ID $parentId = null): void {
    $existingTag = $this->tagRepository->findByNameAndParent($name->getValue(), $userId, $parentId);
    if ($existingTag !== null) {
      throw new DuplicateTagException($name->getValue(), $parentId?->toString());
    }
  }

  public function ensurePathUnique(TagPath $path, ID $userId): void {
    $existingTag = $this->tagRepository->findByPathAndUser($path->getValue(), $userId);
    if ($existingTag !== null) {
      throw new DuplicateTagException($path->getTagName(), null);
    }
  }
}
