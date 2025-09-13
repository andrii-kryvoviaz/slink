<?php

declare(strict_types=1);

namespace Slink\Storage\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

final class StorageUsageMetricsDisabledException extends SpecificationException {
  public function __construct(string $reason = 'Storage usage metrics are disabled for this provider') {
    parent::__construct($reason);
  }

  public function getProperty(): string {
    return 'storage.usage.metrics';
  }
}