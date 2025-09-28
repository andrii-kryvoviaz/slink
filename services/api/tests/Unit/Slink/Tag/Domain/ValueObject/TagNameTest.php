<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Domain\ValueObject;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Tag\Domain\ValueObject\TagName;

final class TagNameTest extends TestCase {

  #[Test]
  public function itCreatesValidTagName(): void {
    $tagName = TagName::fromString('valid-tag_name123');
    
    $this->assertEquals('valid-tag_name123', $tagName->getValue());
  }

  #[Test]
  public function itTrimsWhitespace(): void {
    $tagName = TagName::fromString('  trimmed-tag  ');
    
    $this->assertEquals('trimmed-tag', $tagName->getValue());
  }

  #[Test]
  #[DataProvider('validTagNameProvider')]
  public function itAcceptsValidNames(string $name): void {
    $tagName = TagName::fromString($name);
    
    $this->assertEquals($name, $tagName->getValue());
  }

  #[Test]
  #[DataProvider('invalidTagNameProvider')]
  public function itRejectsInvalidNames(string $name, string $expectedMessage): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage($expectedMessage);
    
    TagName::fromString($name);
  }

  #[Test]
  public function itCreatesFromPayloadWithValue(): void {
    $payload = ['value' => 'payload-tag'];
    $tagName = TagName::fromPayload($payload);
    
    $this->assertEquals('payload-tag', $tagName->getValue());
  }

  #[Test]
  public function itCreatesFromPayloadWithName(): void {
    $payload = ['name' => 'payload-name-tag'];
    $tagName = TagName::fromPayload($payload);
    
    $this->assertEquals('payload-name-tag', $tagName->getValue());
  }

  #[Test]
  public function itConvertsToPayload(): void {
    $tagName = TagName::fromString('test-tag');
    $payload = $tagName->toPayload();
    
    $this->assertEquals(['value' => 'test-tag'], $payload);
  }

  #[Test]
  public function itImplementsToString(): void {
    $tagName = TagName::fromString('string-tag');
    
    $this->assertEquals('{"value":"string-tag"}', $tagName->toString());
  }

  /**
   * @return array<string, array<string>>
   */
  public static function validTagNameProvider(): array {
    return [
      'single character' => ['a'],
      'letters only' => ['tag'],
      'numbers only' => ['123'],
      'with hyphens' => ['tag-name'],
      'with underscores' => ['tag_name'],
      'mixed characters' => ['Tag_Name-123'],
      'uppercase letters' => ['TAG'],
      'max length' => [str_repeat('a', 50)],
    ];
  }

  /**
   * @return array<string, array<string>>
   */
  public static function invalidTagNameProvider(): array {
    return [
      'empty string' => ['', 'Tag name cannot be empty'],
      'only whitespace' => ['   ', 'Tag name cannot be empty'],
      'too long' => [str_repeat('a', 51), 'Tag name must be between 1 and 50 characters'],
      'with spaces' => ['tag name', 'Tag name can only contain letters, numbers, hyphens, and underscores'],
      'with special chars' => ['tag@name', 'Tag name can only contain letters, numbers, hyphens, and underscores'],
      'with dots' => ['tag.name', 'Tag name can only contain letters, numbers, hyphens, and underscores'],
      'with slashes' => ['tag/name', 'Tag name can only contain letters, numbers, hyphens, and underscores'],
      'with unicode' => ['tagñame', 'Tag name can only contain letters, numbers, hyphens, and underscores'],
    ];
  }
}