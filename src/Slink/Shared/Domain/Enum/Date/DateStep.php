<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Enum\Date;

enum DateStep: string {
  case HOUR = 'hour';
  case DAY = 'day';
  case WEEK = 'week';
  case MONTH = 'month';
  case YEAR = 'year';
  
  public function toInterval(): string {
    return match ($this) {
      self::HOUR => 'PT1H',
      self::DAY => 'P1D',
      self::WEEK => 'P1W',
      self::MONTH => 'P1M',
      self::YEAR => 'P1Y',
    };
  }
  
  public function toFormat(): string {
    return match ($this) {
      self::HOUR => 'Y-m-d H:00:00',
      self::DAY => 'Y-m-d',
      self::WEEK => 'o-W',
      self::MONTH => 'Y-m',
      self::YEAR => 'Y',
    };
  }
  
  public function toString(): string {
    return match ($this) {
      self::HOUR => 'Hour',
      self::DAY => 'Day',
      self::WEEK => 'Week',
      self::MONTH => 'Month',
      self::YEAR => 'Year',
    };
  }
}
