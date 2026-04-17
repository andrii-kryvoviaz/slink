<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

final class ShareAccessDeniedException extends SpecificationException {
  public function __construct() {
    parent::__construct('You can only modify your own shares');
  }

  public function getProperty(): string {
    return 'userId';
  }
}
