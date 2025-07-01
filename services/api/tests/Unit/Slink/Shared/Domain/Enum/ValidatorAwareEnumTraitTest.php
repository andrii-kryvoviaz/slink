<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Enum;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

final class ValidatorAwareEnumTraitTest extends TestCase {

  #[Test]
  public function itReturnsArrayOfValues(): void {
    $values = TestEnum::values();

    $expected = ['value1', 'value2', 'value3'];
    $this->assertEquals($expected, $values);
  }

  #[Test]
  public function itReturnsEmptyArrayForEnumWithNoCases(): void {
    $values = EmptyTestEnum::values();

    $this->assertEquals([], $values);
  }

  #[Test]
  public function itReturnsStringsForAllTypes(): void {
    $stringValues = StringTestEnum::values();

    $this->assertNotEmpty($stringValues);
  }

  #[Test]
  public function itWorksWithStringEnum(): void {
    $values = StringTestEnum::values();

    $expected = ['first', 'second'];
    $this->assertEquals($expected, $values);
  }
}

enum TestEnum: string {
  use ValidatorAwareEnumTrait;

  case First = 'value1';
  case Second = 'value2';
  case Third = 'value3';
}

enum EmptyTestEnum: string {
  use ValidatorAwareEnumTrait;
}

enum StringTestEnum: string {
  use ValidatorAwareEnumTrait;

  case Alpha = 'first';
  case Beta = 'second';
}
