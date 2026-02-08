<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

final class TagMoveNotAllowedException extends SpecificationException {
  public function __construct(string $message) {
    parent::__construct($message);
  }

  public function getProperty(): string {
    return 'parentId';
  }
}
