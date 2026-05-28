<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Exception;

final class ShareExpiredException extends \DomainException {
  public function __construct() {
    parent::__construct('Share has expired');
  }
}
