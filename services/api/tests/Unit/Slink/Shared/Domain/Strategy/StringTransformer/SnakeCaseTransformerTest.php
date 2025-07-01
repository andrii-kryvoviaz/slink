<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Strategy\StringTransformer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Strategy\StringTransformer\SnakeCaseTransformer;

final class SnakeCaseTransformerTest extends TestCase {

  #[Test]
  #[DataProvider('transformationProvider')]
  public function itTransformsStringToSnakeCase(string $input, string $expected): void {
    $transformer = new SnakeCaseTransformer();

    $result = $transformer->transform($input);

    $this->assertEquals($expected, $result);
  }

  /**
   * @return array<int, array<int, string>>
   */
  public static function transformationProvider(): array {
    return [
      ['camelCaseString', 'camel_case_string'],
      ['PascalCaseString', 'pascal_case_string'],
      ['alreadysnakecase', 'alreadysnakecase'],
      ['SimpleWord', 'simple_word'],
      ['XMLParser', 'x_m_l_parser'],
      ['HTTPSConnection', 'h_t_t_p_s_connection'],
      ['userId', 'user_id'],
      ['firstName', 'first_name'],
      ['lastName', 'last_name'],
      ['createdAt', 'created_at'],
      ['updatedAt', 'updated_at'],
      ['ID', 'i_d'],
      ['HTML', 'h_t_m_l'],
      ['APIKey', 'a_p_i_key'],
      ['', ''],
      ['single', 'single']
    ];
  }

  #[Test]
  public function itHandlesComplexCamelCaseStrings(): void {
    $transformer = new SnakeCaseTransformer();

    $result = $transformer->transform('veryLongCamelCasePropertyName');

    $this->assertEquals('very_long_camel_case_property_name', $result);
  }

  #[Test]
  public function itHandlesConsecutiveUppercaseLetters(): void {
    $transformer = new SnakeCaseTransformer();

    $result = $transformer->transform('XMLHttpRequest');

    $this->assertEquals('x_m_l_http_request', $result);
  }

  #[Test]
  public function itHandlesStringWithNumbers(): void {
    $transformer = new SnakeCaseTransformer();

    $result = $transformer->transform('myProperty123');

    $this->assertEquals('my_property123', $result);
  }

  #[Test]
  public function itHandlesStringStartingWithLowercase(): void {
    $transformer = new SnakeCaseTransformer();

    $result = $transformer->transform('propertyName');

    $this->assertEquals('property_name', $result);
  }
}
