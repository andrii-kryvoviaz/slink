<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class S3EndpointNotValidException extends SpecificationException {
  public function __construct(string $message = 'S3 endpoint must be a valid URL.') {
    parent::__construct($message);
  }

  #[\Override]
  function getProperty(): string {
    return 'storage.adapter.s3.endpoint';
  }
}
