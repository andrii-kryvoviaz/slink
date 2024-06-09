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
      self::HOUR => '+1 hour',
      self::DAY => '+1 day',
      self::WEEK => '+1 week',
      self::MONTH => '+1 month',
      self::YEAR => '+1 year',
    };
  }
  
  public function toFormat(): string {
    return match ($this) {
      self::HOUR => 'H:i',
      self::DAY => 'D jS',
      self::WEEK => 'W',
      self::MONTH => 'M',
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
