<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;
use Slink\Shared\Domain\ValueObject\ID;

final class UnauthorizedTagAccessException extends SpecificationException {
  public function __construct(ID $tagId, ID $userId) {
    parent::__construct(
      sprintf('User %s is not authorized to use tag %s', $userId->toString(), $tagId->toString())
    );
  }

  public function getProperty(): string {
    return 'tag_access';
  }
}