<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;
use Slink\Shared\Domain\ValueObject\ID;

final class UnauthorizedImageAccessException extends SpecificationException {
  public function __construct(ID $imageId, ID $userId) {
    parent::__construct(
      sprintf('User %s is not authorized to access image %s', $userId->toString(), $imageId->toString())
    );
  }

  public function getProperty(): string {
    return 'access';
  }
}