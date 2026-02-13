<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Specification;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Exception\InvalidTagMoveException;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;

final readonly class TagCircularMoveSpecification implements TagCircularMoveSpecificationInterface {
  public function __construct(private TagRepositoryInterface $tagRepository) {}

  public function validate(ID $tagId, ID $newParentId): void {
    $tagView = $this->tagRepository->oneById($tagId);
    $newParentView = $this->tagRepository->oneById($newParentId);

    $tagPath = $tagView->getPath();
    $parentPath = $newParentView->getPath();

    if (str_starts_with($parentPath, $tagPath . '/')) {
      throw new InvalidTagMoveException('Cannot move a tag to one of its descendants');
    }
  }
}
