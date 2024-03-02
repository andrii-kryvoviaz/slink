<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

use Slink\Shared\Domain\Exception\DateTimeException;
use DateTimeImmutable;
use Exception;
use Slink\Shared\Infrastructure\Attribute\Groups;
use Slink\Shared\Infrastructure\Attribute\SerializedName;
use Throwable;

final class DateTime extends DateTimeImmutable {
  public const string FORMAT = 'Y-m-d\TH:i:s.uP';
  
  #[Groups(['public'])]
  #[SerializedName('formattedDate')]
  public function getDateString(): string {
    return $this->format('Y-m-d H:i:s');
  }
  
  #[Groups(['public'])]
  #[SerializedName('timestamp')]
  public function getUnixTimeStamp(): int {
    return $this->getTimeStamp();
  }

  /**
   * @throws DateTimeException
   */
  public static function now(): self {
    return self::create();
  }

  /**
   * @throws DateTimeException
   */
  public static function fromString(string $dateTime): self {
    return self::create($dateTime);
  }
  
  /**
   * @throws DateTimeException
   */
  public static function fromTimeStamp(int $timestamp): self {
    return self::create('@' . $timestamp);
  }
  
  /**
   * @throws DateTimeException
   */
  public static function fromUnknown(mixed $dateTime): self {
    if ($dateTime instanceof DateTime) {
      return $dateTime;
    }
    
    if ($dateTime instanceof DateTimeImmutable) {
      return self::fromString($dateTime->format(self::FORMAT));
    }
    
    if (is_string($dateTime)) {
      return self::fromString($dateTime);
    }
    
    if (is_int($dateTime)) {
      return self::fromTimeStamp($dateTime);
    }
    
    throw new DateTimeException(new Exception('Invalid date time format'));
  }

  /**
   * @throws DateTimeException
   */
  private static function create(string $dateTime = ''): self {
    try {
      return new self($dateTime);
    } catch (Throwable $e) {
      throw new DateTimeException(new Exception($e->getMessage(), (int) $e->getCode(), $e));
    }
  }
  
  /**
   * @param DateTime $now
   * @return bool
   */
  public function isBefore(DateTime $now): bool {
    return $this < $now;
  }
  
  /**
   * @param DateTime $now
   * @return bool
   */
  public function isAfter(DateTime $now): bool {
    return $this > $now;
  }
  
  /**
   * @return string
   */
  public function toString(): string {
    return $this->format(self::FORMAT);
  }
}