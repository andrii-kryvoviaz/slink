<?php

declare(strict_types=1);

namespace Slink\Storage\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class SmbConfigurationIncompleteException extends SpecificationException {
  public function __construct(string $message = 'SMB configuration is incomplete.') {
    parent::__construct($message);
  }
  
  #[\Override]
  function getProperty(): string {
    return 'storage.adapter.smb';
  }
}