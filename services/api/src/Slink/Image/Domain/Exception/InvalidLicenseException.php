<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

final class InvalidLicenseException extends SpecificationException {
  public function __construct(string $license) {
    parent::__construct(sprintf('Invalid license type: %s', $license));
  }

  public function getProperty(): string {
    return 'license';
  }
}
