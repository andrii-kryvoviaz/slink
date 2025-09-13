<?php

declare(strict_types=1);

namespace Slink\Storage\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class StorageProviderNotConfiguredException extends SpecificationException {
  public function __construct(string $message = 'Storage provider is not configured.') {
    parent::__construct($message);
  }
  
  #[\Override]
  function getProperty(): string {
    return 'storage.provider';
  }
}