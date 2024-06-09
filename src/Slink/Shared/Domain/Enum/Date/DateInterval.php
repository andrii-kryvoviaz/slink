<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Enum\Date;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum DateInterval: string {
  use ValidatorAwareEnumTrait;
  
  case TODAY = 'today';
  case CURRENT_WEEK = 'current_week';
  case LAST_WEEK = 'last_week';
  case LAST_7_DAYS = 'last_7_days';
  case CURRENT_MONTH = 'current_month';
  case LAST_MONTH = 'last_month';
  case LAST_30_DAYS = 'last_30_days';
  case CURRENT_YEAR = 'current_year';
  case ALL_TIME = 'all_time';
  
  public function toString(): string {
    return match ($this) {
      self::TODAY => 'Today',
      self::CURRENT_WEEK => 'Current Week',
      self::LAST_WEEK => 'Last Week',
      self::LAST_7_DAYS => 'Last 7 Days',
      self::CURRENT_MONTH => 'Current Month',
      self::LAST_MONTH => 'Last Month',
      self::LAST_30_DAYS => 'Last 30 Days',
      self::CURRENT_YEAR => 'Current Year',
      self::ALL_TIME => 'All Time',
    };
  }
  
  /**
   * @return array<string, string>
   */
  public static function all(): array {
    /** @var DateInterval[] $cases */
    $cases = self::cases(); // @phpstan-ignore-line
    
    return array_reduce(
      $cases,
      function ($carry, $item) {
        $carry[$item->value] = $item->toString();
        return $carry;
      },
      []
    );
  }
}
