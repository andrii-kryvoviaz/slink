<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Strategy\StringTransformer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Strategy\StringTransformer\CamelCaseTransformer;

final class CamelCaseTransformerTest extends TestCase {

  #[Test]
  #[DataProvider('transformationProvider')]
  public function itTransformsStringToCamelCase(string $input, string $expected): void {
    $transformer = new CamelCaseTransformer();

    $result = $transformer->transform($input);

    $this->assertEquals($expected, $result);
  }

  /**
   * @return array<int, array<int, string>>
   */
  public static function transformationProvider(): array {
    return [
      ['snake_case_string', 'snakeCaseString'],
      ['kebab-case-string', 'kebabCaseString'],
      ['mixed_case-string', 'mixedCaseString'],
      ['single', 'single'],
      ['UPPERCASE', 'uPPERCASE'],
      ['already_camelCase', 'alreadyCamelCase'],
      ['with_multiple___underscores', 'withMultipleUnderscores'],
      ['with---multiple-dashes', 'withMultipleDashes'],
      ['mixed_case-and_symbols', 'mixedCaseAndSymbols'],
      ['', ''],
      ['123_numeric_start', '123NumericStart'],
      ['user_id', 'userId'],
      ['created_at', 'createdAt'],
      ['first_name', 'firstName'],
      ['last_name', 'lastName']
    ];
  }

  #[Test]
  public function itHandlesComplexStrings(): void {
    $transformer = new CamelCaseTransformer();

    $result = $transformer->transform('very_long_snake_case_property_name');

    $this->assertEquals('veryLongSnakeCasePropertyName', $result);
  }

  #[Test]
  public function itHandlesConsecutiveDelimiters(): void {
    $transformer = new CamelCaseTransformer();

    $result = $transformer->transform('multiple__underscores___here');

    $this->assertEquals('multipleUnderscoresHere', $result);
  }

  #[Test]
  public function itHandlesMixedDelimiters(): void {
    $transformer = new CamelCaseTransformer();

    $result = $transformer->transform('mixed_delimiters-and_separators');

    $this->assertEquals('mixedDelimitersAndSeparators', $result);
  }
}
