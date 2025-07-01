<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Enum\Date;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Enum\Date\DateInterval;
use ValueError;

final class DateIntervalTest extends TestCase {

  /**
   * @return array<int, array<int, mixed>>
   */
  public static function stringProvider(): array {
    return [
      [DateInterval::TODAY, 'Today'],
      [DateInterval::CURRENT_WEEK, 'Current Week'],
      [DateInterval::LAST_WEEK, 'Last Week'],
      [DateInterval::LAST_7_DAYS, 'Last 7 Days'],
      [DateInterval::CURRENT_MONTH, 'Current Month'],
      [DateInterval::LAST_MONTH, 'Last Month'],
      [DateInterval::LAST_30_DAYS, 'Last 30 Days'],
      [DateInterval::CURRENT_YEAR, 'Current Year'],
      [DateInterval::ALL_TIME, 'All Time']
    ];
  }

  #[Test]
  public function itCanBeCreatedFromString(): void {
    $this->assertEquals(DateInterval::TODAY, DateInterval::from('today'));
    $this->assertEquals(DateInterval::CURRENT_WEEK, DateInterval::from('current_week'));
    $this->assertEquals(DateInterval::LAST_WEEK, DateInterval::from('last_week'));
    $this->assertEquals(DateInterval::LAST_7_DAYS, DateInterval::from('last_7_days'));
    $this->assertEquals(DateInterval::CURRENT_MONTH, DateInterval::from('current_month'));
    $this->assertEquals(DateInterval::LAST_MONTH, DateInterval::from('last_month'));
    $this->assertEquals(DateInterval::LAST_30_DAYS, DateInterval::from('last_30_days'));
    $this->assertEquals(DateInterval::CURRENT_YEAR, DateInterval::from('current_year'));
    $this->assertEquals(DateInterval::ALL_TIME, DateInterval::from('all_time'));
  }

  #[Test]
  public function itCanTryFromString(): void {
    $this->assertEquals(DateInterval::TODAY, DateInterval::tryFrom('today'));
    $this->assertEquals(DateInterval::ALL_TIME, DateInterval::tryFrom('all_time'));

    $result = DateInterval::tryFrom('nonexistent_value');
    $this->assertEmpty($result);
  }

  #[Test]
  public function itHasAllExpectedCases(): void {
    $cases = DateInterval::cases();

    $this->assertCount(9, $cases);
    $this->assertContains(DateInterval::TODAY, $cases);
    $this->assertContains(DateInterval::CURRENT_WEEK, $cases);
    $this->assertContains(DateInterval::LAST_WEEK, $cases);
    $this->assertContains(DateInterval::LAST_7_DAYS, $cases);
    $this->assertContains(DateInterval::CURRENT_MONTH, $cases);
    $this->assertContains(DateInterval::LAST_MONTH, $cases);
    $this->assertContains(DateInterval::LAST_30_DAYS, $cases);
    $this->assertContains(DateInterval::CURRENT_YEAR, $cases);
    $this->assertContains(DateInterval::ALL_TIME, $cases);
  }

  #[Test]
  public function itHasCorrectValues(): void {
    $this->assertEquals('today', DateInterval::TODAY->value);
    $this->assertEquals('current_week', DateInterval::CURRENT_WEEK->value);
    $this->assertEquals('last_week', DateInterval::LAST_WEEK->value);
    $this->assertEquals('last_7_days', DateInterval::LAST_7_DAYS->value);
    $this->assertEquals('current_month', DateInterval::CURRENT_MONTH->value);
    $this->assertEquals('last_month', DateInterval::LAST_MONTH->value);
    $this->assertEquals('last_30_days', DateInterval::LAST_30_DAYS->value);
    $this->assertEquals('current_year', DateInterval::CURRENT_YEAR->value);
    $this->assertEquals('all_time', DateInterval::ALL_TIME->value);
  }

  #[Test]
  public function itImplementsValidatorAwareEnumTrait(): void {
    $values = DateInterval::values();

    $expected = [
      'today',
      'current_week',
      'last_week',
      'last_7_days',
      'current_month',
      'last_month',
      'last_30_days',
      'current_year',
      'all_time'
    ];

    $this->assertEquals($expected, $values);
  }

  #[Test]
  public function itReturnsAllIntervals(): void {
    $all = DateInterval::all();

    $expected = [
      'today' => 'Today',
      'current_week' => 'Current Week',
      'last_week' => 'Last Week',
      'last_7_days' => 'Last 7 Days',
      'current_month' => 'Current Month',
      'last_month' => 'Last Month',
      'last_30_days' => 'Last 30 Days',
      'current_year' => 'Current Year',
      'all_time' => 'All Time'
    ];

    $this->assertEquals($expected, $all);
  }

  #[Test]
  #[DataProvider('stringProvider')]
  public function itReturnsCorrectString(DateInterval $interval, string $expectedString): void {
    $this->assertEquals($expectedString, $interval->toString());
  }

  #[Test]
  public function itThrowsExceptionForInvalidValue(): void {
    $this->expectException(ValueError::class);

    DateInterval::from('invalid');
  }
}
