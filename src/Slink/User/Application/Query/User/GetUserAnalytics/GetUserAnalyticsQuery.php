<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\User\GetUserAnalytics;

use Slink\Shared\Application\Query\QueryInterface;

final readonly class GetUserAnalyticsQuery implements QueryInterface {
  public function __construct(
    private ?string $key = null
  ) {
  }
  
  public function key(): ?string {
    return $this->key;
  }
}