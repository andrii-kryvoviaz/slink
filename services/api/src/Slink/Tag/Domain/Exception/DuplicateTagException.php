<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

final class DuplicateTagException extends SpecificationException {
  public function __construct(string $tagName, ?string $parentId = null) {
    $message = $parentId
      ? sprintf('Tag "%s" already exists under this parent', $tagName)
      : sprintf('Tag "%s" already exists', $tagName);

    parent::__construct($message);
  }

  public function getProperty(): string {
    return 'name';
  }
}