<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class S3CredentialsNotConfiguredException extends SpecificationException {
  private string $field;
  
  public function __construct(string $field) {
    $this->field = $field;
    $label = $field === 'key' ? 'Access key' : 'Secret key';
    parent::__construct("S3 {$label} is required.");
  }
  
  #[\Override]
  function getProperty(): string {
    return "storage.adapter.s3.{$this->field}";
  }
}
