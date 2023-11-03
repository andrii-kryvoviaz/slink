<?php

declare(strict_types=1);

namespace Slik\Shared\Domain\ValueObject;

use Slik\Shared\Domain\Exception\DateTimeException;
use DateTimeImmutable;
use Exception;
use Throwable;

final class DateTime extends DateTimeImmutable {
  public const FORMAT = 'Y-m-d\TH:i:s.uP';

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

  public function toString(): string {
    return $this->format(self::FORMAT);
  }
}