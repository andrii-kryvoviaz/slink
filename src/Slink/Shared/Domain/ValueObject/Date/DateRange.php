<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject\Date;

use Slink\Shared\Domain\Enum\Date\DateInterval;
use Slink\Shared\Domain\Enum\Date\DateStep;
use Slink\Shared\Domain\Exception\Date\DateIntervalException;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class DateRange extends AbstractValueObject {
  /**
   * @param DateTime $start
   * @param DateTime $end
   */
  private function __construct(
    private DateTime $start,
    private DateTime $end,
  ) {
    $this->validate();
  }
  
  /**
   * @param DateTime $start
   * @param DateTime $end
   * @return self
   */
  public static function create(DateTime $start, DateTime $end): self {
    return new self($start, $end);
  }
  
  /**
   * @throws DateTimeException
   */
  public static function fromString(string $start, string $end): self {
    return new self(DateTime::fromString($start), DateTime::fromString($end));
  }
  
  /**
   * @param DateInterval $dateInterval
   * @return DateRange
   * @throws DateTimeException
   * @throws \DateMalformedStringException
   */
  public static function fromDateInterval(DateInterval $dateInterval): self {
    $start = DateTime::now()->setTime(0, 0, 0);
    $end = DateTime::now();
    
    switch($dateInterval) {
      case DateInterval::TODAY:
        $start = $start->modify('today');
        break;
      case DateInterval::CURRENT_WEEK:
        $start = $start->modify('this week');
        break;
      case DateInterval::LAST_WEEK:
        $start = $start->modify('last week');
        $end = $end->modify('last week')->modify('sunday');
        break;
      case DateInterval::LAST_7_DAYS:
        $start = $start->modify('-7 days');
        break;
      case DateInterval::CURRENT_MONTH:
        $start = $start->modify('first day of this month');
        break;
      case DateInterval::LAST_MONTH:
        $start = $start->modify('first day of last month');
        $end = $end->modify('last day of last month');
        break;
      case DateInterval::LAST_30_DAYS:
        $start = $start->modify('-30 days');
        break;
      case DateInterval::CURRENT_YEAR:
        $start = $start->modify('first day of january this year');
        break;
      case DateInterval::ALL_TIME:
        $start = DateTime::fromTimeStamp(0);
        break;
    }
    
    return new self($start, $end);
  }
  
  /**
   * @return DateTime
   */
  public function getStart(): DateTime {
    return $this->start;
  }
  
  /**
   * @return DateTime
   */
  public function getEnd(): DateTime {
    return $this->end;
  }
  
  /**
   * @return DateStep
   */
  public function getStep(): DateStep {
    $diff = $this->start->diff($this->end);
    
    if($diff->days === 0) {
      return DateStep::HOUR;
    }
    
    if($diff->days <= 31) {
      return DateStep::DAY;
    }
    
    if($diff->days <= 365) {
      return DateStep::MONTH;
    }
    
    return DateStep::YEAR;
  }
  
  /**
   * @return array<DateRange>
   * @throws \DateMalformedStringException
   */
  public function generateIntervals(): array {
    $step = $this->getStep();
    $intervals = [];
    
    $current = clone $this->start;
    $end = clone $this->end;
    
    while($current->isBefore($end)) {
      $next = clone $current;
      
      $next = match ($step) {
        DateStep::HOUR => $next->modify('+1 hour'),
        DateStep::DAY => $next->modify('+1 day'),
        DateStep::MONTH => $next->modify('+1 month'),
        DateStep::YEAR => $next->modify('+1 year'),
        default => throw new DateIntervalException('Invalid date step'),
      };
      
      $intervals[] = self::create($current, $next);
      $current = $next;
    }
    
    return $intervals;
  }
  
  /**
   * @return void
   */
  private function validate(): void {
    if($this->start->isAfter($this->end)) {
      throw new DateIntervalException('Start date must be before end date');
    }
  }
}