<?php

namespace Unit\Slink\Shared\Domain\ValueObject;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateTime;

final class DateTimeTest extends TestCase {

  /**
   * @return array<int, array<int, mixed>>
   */
  public static function isBeforeProvider(): array {
    return [
      ['2024-01-01T00:00:00.000000+00:00', '2024-01-01T00:00:00.000000+00:00', false],
      ['2024-01-01T00:00:00.000000+00:00', '2024-01-01T00:00:00.000001+00:00', true],
      ['2024-01-01T00:00:00.000000+00:00', '2024-01-01T00:00:00.000000-00:01', true],
      [1640995200, 1640995201, true],
      [1640995200, 1640995199, false],
    ];
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  #[DataProvider('isBeforeProvider')]
  public function itChecksIfDateTimeIsAfter(mixed $dateTimeBefore, mixed $dateTimeAfter, bool $expected): void {
    $dateTime1 = DateTime::fromUnknown($dateTimeBefore);
    $dateTime2 = DateTime::fromUnknown($dateTimeAfter);
    $this->assertEquals($expected, $dateTime2->isAfter($dateTime1));
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  #[DataProvider('isBeforeProvider')]
  public function itChecksIfDateTimeIsBefore(mixed $dateTimeBefore, mixed $dateTimeAfter, bool $expected): void {
    $dateTime1 = DateTime::fromUnknown($dateTimeBefore);
    $dateTime2 = DateTime::fromUnknown($dateTimeAfter);
    $this->assertEquals($expected, $dateTime1->isBefore($dateTime2));
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itConvertsDateTimeToString(): void {
    $dateTime = DateTime::fromString('2024-01-01T00:00:00.000000+00:00');
    $this->assertEquals('2024-01-01T00:00:00.000000+00:00', $dateTime->toString());
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itCreatesDateTimeFromNow(): void {
    $dateTime = DateTime::now();
    $this->assertInstanceOf(DateTime::class, $dateTime);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itCreatesDateTimeFromString(): void {
    $dateTime = DateTime::fromString('2024-01-01T00:00:00.000000+00:00');
    $this->assertInstanceOf(DateTime::class, $dateTime);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itCreatesDateTimeFromTimeStamp(): void {
    $dateTime = DateTime::fromTimeStamp(1640995200);
    $this->assertInstanceOf(DateTime::class, $dateTime);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itCreatesDateTimeFromUnknown(): void {
    $dateTime = DateTime::fromUnknown('2024-01-01T00:00:00.000000+00:00');
    $this->assertInstanceOf(DateTime::class, $dateTime);
  }

  /**
   * @throws DateTimeException
   */
  #[Test]
  public function itThrowsExceptionForInvalidDateTimeFormat(): void {
    $this->expectException(DateTimeException::class);
    DateTime::fromUnknown('invalid format');
  }
}
