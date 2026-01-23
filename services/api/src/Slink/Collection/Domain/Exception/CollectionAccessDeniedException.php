<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

final class CollectionAccessDeniedException extends SpecificationException {
  public function __construct() {
    parent::__construct('You can only access your own collections');
  }

  public function getProperty(): string {
    return 'userId';
  }
}
