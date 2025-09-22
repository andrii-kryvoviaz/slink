<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Exception;

use InvalidArgumentException;

final class DuplicateTagException extends InvalidArgumentException {
  public function __construct(string $tagName, ?string $parentId = null) {
    $message = $parentId
      ? sprintf('Tag "%s" already exists under parent "%s"', $tagName, $parentId)
      : sprintf('Root tag "%s" already exists', $tagName);

    parent::__construct($message);
  }
}