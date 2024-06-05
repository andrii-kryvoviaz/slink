<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

interface UserAnalyticsRepositoryInterface {
  
  /**
   * @return array<string, mixed>
   */
  public function getAnalytics(): array;
}