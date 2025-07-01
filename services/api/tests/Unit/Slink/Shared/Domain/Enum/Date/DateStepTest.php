<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Enum\Date;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Enum\Date\DateStep;
use ValueError;

final class DateStepTest extends TestCase {

  /**
   * @return array<int, array<int, mixed>>
   */
  public static function formatProvider(): array {
    return [
      [DateStep::HOUR, 'H:i'],
      [DateStep::DAY, 'D jS'],
      [DateStep::WEEK, 'W'],
      [DateStep::MONTH, 'M'],
      [DateStep::YEAR, 'Y']
    ];
  }

  /**
   * @return array<int, array<int, mixed>>
   */
  public static function intervalProvider(): array {
    return [
      [DateStep::HOUR, '+1 hour'],
      [DateStep::DAY, '+1 day'],
      [DateStep::WEEK, '+1 week'],
      [DateStep::MONTH, '+1 month'],
      [DateStep::YEAR, '+1 year']
    ];
  }

  /**
   * @return array<int, array<int, mixed>>
   */
  public static function stringProvider(): array {
    return [
      [DateStep::HOUR, 'Hour'],
      [DateStep::DAY, 'Day'],
      [DateStep::WEEK, 'Week'],
      [DateStep::MONTH, 'Month'],
      [DateStep::YEAR, 'Year']
    ];
  }

  #[Test]
  public function itCanBeCreatedFromString(): void {
    $this->assertEquals(DateStep::HOUR, DateStep::from('hour'));
    $this->assertEquals(DateStep::DAY, DateStep::from('day'));
    $this->assertEquals(DateStep::WEEK, DateStep::from('week'));
    $this->assertEquals(DateStep::MONTH, DateStep::from('month'));
    $this->assertEquals(DateStep::YEAR, DateStep::from('year'));
  }

  #[Test]
  public function itCanTryFromString(): void {
    $this->assertEquals(DateStep::HOUR, DateStep::tryFrom('hour'));
    $this->assertEquals(DateStep::DAY, DateStep::tryFrom('day'));
    $this->assertEquals(DateStep::WEEK, DateStep::tryFrom('week'));
    $this->assertEquals(DateStep::MONTH, DateStep::tryFrom('month'));
    $this->assertEquals(DateStep::YEAR, DateStep::tryFrom('year'));

    $result = DateStep::tryFrom('nonexistent_value');
    $this->assertEmpty($result);
  }

  #[Test]
  public function itHasAllExpectedCases(): void {
    $cases = DateStep::cases();

    $this->assertCount(5, $cases);
    $this->assertContains(DateStep::HOUR, $cases);
    $this->assertContains(DateStep::DAY, $cases);
    $this->assertContains(DateStep::WEEK, $cases);
    $this->assertContains(DateStep::MONTH, $cases);
    $this->assertContains(DateStep::YEAR, $cases);
  }

  #[Test]
  public function itHasCorrectValues(): void {
    $this->assertEquals('hour', DateStep::HOUR->value);
    $this->assertEquals('day', DateStep::DAY->value);
    $this->assertEquals('week', DateStep::WEEK->value);
    $this->assertEquals('month', DateStep::MONTH->value);
    $this->assertEquals('year', DateStep::YEAR->value);
  }

  #[Test]
  #[DataProvider('formatProvider')]
  public function itReturnsCorrectFormat(DateStep $step, string $expectedFormat): void {
    $this->assertEquals($expectedFormat, $step->toFormat());
  }

  #[Test]
  #[DataProvider('intervalProvider')]
  public function itReturnsCorrectInterval(DateStep $step, string $expectedInterval): void {
    $this->assertEquals($expectedInterval, $step->toInterval());
  }

  #[Test]
  #[DataProvider('stringProvider')]
  public function itReturnsCorrectString(DateStep $step, string $expectedString): void {
    $this->assertEquals($expectedString, $step->toString());
  }

  #[Test]
  public function itThrowsExceptionForInvalidValue(): void {
    $this->expectException(ValueError::class);

    DateStep::from('invalid');
  }
}
