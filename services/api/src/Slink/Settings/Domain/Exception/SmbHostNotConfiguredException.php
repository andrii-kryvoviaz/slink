<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class SmbHostNotConfiguredException extends SpecificationException {
  public function __construct(string $message = 'SMB host is required.') {
    parent::__construct($message);
  }

  #[\Override]
  function getProperty(): string {
    return 'storage.adapter.smb.host';
  }
}
