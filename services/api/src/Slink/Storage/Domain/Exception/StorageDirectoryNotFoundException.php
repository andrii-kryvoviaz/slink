<?php

declare(strict_types=1);

namespace Slink\Storage\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class StorageDirectoryNotFoundException extends SpecificationException {
  public function __construct(string $directory, ?string $message = null) {
    parent::__construct($message ?? "Storage directory not found or not accessible: {$directory}");
  }
  
  #[\Override]
  function getProperty(): string {
    return 'storage.directory';
  }
}