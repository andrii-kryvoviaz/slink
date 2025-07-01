<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Strategy\StringTransformer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Strategy\StringTransformer\PassthroughTransformer;

final class PassthroughTransformerTest extends TestCase {

  #[Test]
  public function itReturnsOriginalString(): void {
    $transformer = new PassthroughTransformer();
    $input = 'originalString';

    $result = $transformer->transform($input);

    $this->assertEquals($input, $result);
  }

  #[Test]
  public function itHandlesEmptyString(): void {
    $transformer = new PassthroughTransformer();

    $result = $transformer->transform('');

    $this->assertEquals('', $result);
  }

  #[Test]
  public function itHandlesSpecialCharacters(): void {
    $transformer = new PassthroughTransformer();
    $input = 'special-chars_with.symbols!@#$%^&*()';

    $result = $transformer->transform($input);

    $this->assertEquals($input, $result);
  }

  #[Test]
  public function itHandlesUnicodeCharacters(): void {
    $transformer = new PassthroughTransformer();
    $input = 'café_résumé_naïve';

    $result = $transformer->transform($input);

    $this->assertEquals($input, $result);
  }

  #[Test]
  public function itHandlesNumericStrings(): void {
    $transformer = new PassthroughTransformer();
    $input = '123456789';

    $result = $transformer->transform($input);

    $this->assertEquals($input, $result);
  }

  #[Test]
  public function itHandlesMixedContent(): void {
    $transformer = new PassthroughTransformer();
    $input = 'Mixed123_content-with.SYMBOLS!';

    $result = $transformer->transform($input);

    $this->assertEquals($input, $result);
  }

  #[Test]
  public function itPreservesWhitespace(): void {
    $transformer = new PassthroughTransformer();
    $input = '  spaces  and  tabs	';

    $result = $transformer->transform($input);

    $this->assertEquals($input, $result);
  }
}
