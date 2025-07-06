<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Exception\InvalidDisplayNameException;
use Slink\User\Domain\ValueObject\DisplayName;

final class DisplayNameTest extends TestCase {

  /**
   * @return array<string, array{string, string}>
   */
  public static function provideInvalidDisplayNames(): array {
    return [
      'Too short - 2 chars' => ['ab', 'Display name must be at least 3 characters long'],
      'Too short - 1 char' => ['a', 'Display name must be at least 3 characters long'],
      'Empty string' => ['', 'Display name must be at least 3 characters long'],
      'Too long - 31 chars' => [str_repeat('a', 31), 'Display name must be at most 30 characters long'],
      'Too long - 50 chars' => [str_repeat('x', 50), 'Display name must be at most 30 characters long'],
    ];
  }

  /**
   * @return array<string, array{string}>
   */
  public static function provideReservedNames(): array {
    return [
      'Anonymous exact match' => ['Anonymous'],
      'Anonymous uppercase' => ['ANONYMOUS'],
      'Anonymous lowercase' => ['anonymous'],
      'Anonymous mixed case' => ['AnOnYmOuS'],
      'Guest exact match' => ['Guest'],
      'Guest uppercase' => ['GUEST'],
      'Guest lowercase' => ['guest'],
      'Guest mixed case' => ['GuEsT'],
    ];
  }

  /**
   * @return array<string, array{string}>
   */
  public static function provideValidDisplayNames(): array {
    return [
      'Minimum length' => ['abc'],
      'Standard name' => ['John Doe'],
      'Hyphenated name' => ['Jane Smith-Brown'],
      'With numbers' => ['User123'],
      'With spaces' => ['Test User'],
      'Initials' => ['A B C'],
      'With apostrophe' => ['O\'Connor'],
      'Unicode characters' => ['María González'],
      'Long hyphenated' => ['Jean-Claude Van Damme'],
      'Maximum length' => [str_repeat('a', 30)],
      'Mixed case' => ['CamelCase Name'],
      'With dots' => ['Mr. John Doe Jr.'],
    ];
  }

  #[Test]
  public function itCreatesDisplayNameFromNull(): void {
    $displayName = DisplayName::fromNullableString(null);

    $this->assertInstanceOf(DisplayName::class, $displayName);
    $this->assertSame('Guest', $displayName->toString());
  }

  #[Test]
  public function itCreatesDisplayNameFromNullableString(): void {
    $nameString = 'John Doe';
    $displayName = DisplayName::fromNullableString($nameString);

    $this->assertInstanceOf(DisplayName::class, $displayName);
    $this->assertSame($nameString, $displayName->toString());
  }

  #[Test]
  public function itCreatesValidDisplayName(): void {
    $nameString = 'John Doe';
    $displayName = DisplayName::fromString($nameString);

    $this->assertInstanceOf(DisplayName::class, $displayName);
    $this->assertSame($nameString, $displayName->toString());
  }

  #[Test]
  #[DataProvider('provideValidDisplayNames')]
  public function itCreatesValidDisplayNamesFromDifferentFormats(string $nameString): void {
    $displayName = DisplayName::fromString($nameString);

    $this->assertInstanceOf(DisplayName::class, $displayName);
    $this->assertSame($nameString, $displayName->toString());
  }

  #[Test]
  public function itHandlesDisplayNameEquality(): void {
    $name1 = DisplayName::fromString('John Doe');
    $name2 = DisplayName::fromString('John Doe');
    $name3 = DisplayName::fromString('Jane Doe');

    $this->assertSame($name1->toString(), $name2->toString());
    $this->assertNotSame($name1->toString(), $name3->toString());
  }

  #[Test]
  public function itHandlesSpecialCharacters(): void {
    $specialName = 'José María O\'Connor-Smith';
    $displayName = DisplayName::fromString($specialName);

    $this->assertSame($specialName, $displayName->toString());
  }

  #[Test]
  public function itPreservesWhitespace(): void {
    $nameWithSpaces = '  John   Doe  ';
    $displayName = DisplayName::fromString($nameWithSpaces);

    $this->assertSame($nameWithSpaces, $displayName->toString());
  }

  #[Test]
  #[DataProvider('provideInvalidDisplayNames')]
  public function itThrowsExceptionForInvalidDisplayName(string $invalidName, string $expectedMessage): void {
    $this->expectException(InvalidDisplayNameException::class);
    $this->expectExceptionMessage($expectedMessage);

    DisplayName::fromString($invalidName);
  }

  #[Test]
  #[DataProvider('provideReservedNames')]
  public function itThrowsExceptionForReservedName(string $reservedName): void {
    $this->expectException(InvalidDisplayNameException::class);
    
    $expectedReservedName = strtolower($reservedName) === 'anonymous' || stripos($reservedName, 'anonymous') !== false ? 'Anonymous' : 'Guest';
    $this->expectExceptionMessage("`{$expectedReservedName}` is a reserved display name");

    DisplayName::fromString($reservedName);
  }
}
