<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Specification;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Exception\InvalidTagMoveException;

interface TagCircularMoveSpecificationInterface {
  /**
   * @throws InvalidTagMoveException if circular move detected
   */
  public function validate(ID $tagId, ID $newParentId): void;
}
