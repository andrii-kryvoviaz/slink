<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

final class TagAccessDeniedException extends SpecificationException {
  public function __construct() {
    parent::__construct('You can only access your own tags');
  }

  public function getProperty(): string {
    return 'userId';
  }
}
