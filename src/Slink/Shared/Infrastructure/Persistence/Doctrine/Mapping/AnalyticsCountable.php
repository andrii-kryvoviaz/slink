<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Mapping;

use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateTime;

final readonly class AnalyticsCountable {
  private DateTime $date;
  
  /**
   * @throws DateTimeException
   */
  public function __construct(
    string $date,
    private int $count,
  ) {
    try {
      $this->date = DateTime::create($date, new \DateTimeZone('UTC'))
        ->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    } catch (\DateInvalidTimeZoneException $e) {
      $this->date = DateTime::fromString($date);
    }
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return self
   * @throws DateTimeException
   */
  public static function fromPayload(array $payload): self {
    return new self($payload['date'], (int) $payload['count']);
  }
  
  public function getDate(): DateTime {
    return $this->date;
  }
  
  public function getFormattedDate(string $format): string {
    return $this->date->format($format);
  }
  
  public function getCount(): int {
    return $this->count;
  }
}