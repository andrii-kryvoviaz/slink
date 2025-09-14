<?php

declare(strict_types=1);

namespace Slink\Storage\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class S3BucketNotConfiguredException extends SpecificationException {
  public function __construct(string $message = 'S3 bucket is not configured.') {
    parent::__construct($message);
  }
  
  #[\Override]
  function getProperty(): string {
    return 'storage.adapter.s3.bucket';
  }
}