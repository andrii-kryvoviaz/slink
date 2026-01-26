<?php

declare(strict_types=1);

namespace Slink\Tests\Unit\Slink\Shared\Domain\ValueObject;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\EscapedString;

final class EscapedStringTest extends TestCase {
  #[Test]
  public function itCreatesFromString(): void {
    $escaped = EscapedString::fromString('Hello World');

    $this->assertSame('Hello World', $escaped->getValue());
    $this->assertSame('Hello World', $escaped->toString());
  }

  #[Test]
  public function itCreatesFromTrusted(): void {
    $escaped = EscapedString::fromTrusted('&lt;script&gt;');

    $this->assertSame('&lt;script&gt;', $escaped->getValue());
  }

  #[Test]
  public function itCreatesEmpty(): void {
    $escaped = EscapedString::empty();

    $this->assertTrue($escaped->isEmpty());
    $this->assertSame('', $escaped->getValue());
  }

  #[Test]
  public function itCalculatesLengthCorrectly(): void {
    $escaped = EscapedString::fromString('<script>');

    $this->assertSame(8, $escaped->length());
  }

  #[Test]
  #[DataProvider('xssPayloadProvider')]
  public function itEscapesXssPayloads(string $payload, string $expected): void {
    $escaped = EscapedString::fromString($payload);

    $this->assertSame($expected, $escaped->getValue());
    $this->assertStringNotContainsString('<script>', $escaped->getValue());
  }

  #[Test]
  public function itPreventsDoubleEncoding(): void {
    $alreadyEncoded = '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;';
    $escaped = EscapedString::fromString($alreadyEncoded);

    $this->assertSame($alreadyEncoded, $escaped->getValue());
    $this->assertStringNotContainsString('&amp;lt;', $escaped->getValue());
  }

  #[Test]
  public function itHandlesMultipleEncodingAttempts(): void {
    $original = '<script>alert("xss")</script>';
    $escaped1 = EscapedString::fromString($original);
    $escaped2 = EscapedString::fromString($escaped1->getValue());
    $escaped3 = EscapedString::fromString($escaped2->getValue());

    $this->assertSame($escaped1->getValue(), $escaped2->getValue());
    $this->assertSame($escaped2->getValue(), $escaped3->getValue());
  }

  #[Test]
  public function itPreservesNormalText(): void {
    $text = 'This is a normal description with some punctuation: Hello, World!';
    $escaped = EscapedString::fromString($text);

    $this->assertSame($text, $escaped->getValue());
  }

  #[Test]
  public function itPreservesUnicodeCharacters(): void {
    $text = 'Hello 世界 🌍 Привет';
    $escaped = EscapedString::fromString($text);

    $this->assertSame($text, $escaped->getValue());
  }

  /**
   * @return iterable<string, array{string, string}>
   */
  public static function xssPayloadProvider(): iterable {
    yield 'basic script tag' => [
      '<script>alert("xss")</script>',
      '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
    ];

    yield 'script with single quotes' => [
      "<script>alert('xss')</script>",
      "&lt;script&gt;alert(&apos;xss&apos;)&lt;/script&gt;",
    ];

    yield 'img onerror' => [
      '<img src=x onerror="alert(1)">',
      '&lt;img src=x onerror=&quot;alert(1)&quot;&gt;',
    ];

    yield 'svg onload' => [
      '<svg onload="alert(1)">',
      '&lt;svg onload=&quot;alert(1)&quot;&gt;',
    ];

    yield 'body onload' => [
      '<body onload="alert(1)">',
      '&lt;body onload=&quot;alert(1)&quot;&gt;',
    ];

    yield 'iframe injection' => [
      '<iframe src="javascript:alert(1)">',
      '&lt;iframe src=&quot;javascript:alert(1)&quot;&gt;',
    ];

    yield 'event handler' => [
      '<div onclick="alert(1)">click me</div>',
      '&lt;div onclick=&quot;alert(1)&quot;&gt;click me&lt;/div&gt;',
    ];

    yield 'mixed content' => [
      'Hello <script>evil()</script> World',
      'Hello &lt;script&gt;evil()&lt;/script&gt; World',
    ];

    yield 'nested tags' => [
      '<<script>script>alert(1)<</script>/script>',
      '&lt;&lt;script&gt;script&gt;alert(1)&lt;&lt;/script&gt;/script&gt;',
    ];

    yield 'ampersand' => [
      'Tom & Jerry',
      'Tom &amp; Jerry',
    ];
  }
}
