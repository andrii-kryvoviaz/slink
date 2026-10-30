<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Exception\InvalidUsernameException;
use Slink\User\Domain\ValueObject\Username;

final class UsernameTest extends TestCase {

  /**
   * @return array<string, array{string, string}>
   */
  public static function provideInvalidUsernames(): array {
    return [
      'Too short - 2 chars' => ['ab', 'Username must be at least 3 characters long'],
      'Too short - 1 char' => ['a', 'Username must be at least 3 characters long'],
      'Empty string' => ['', 'Username must be at least 3 characters long'],
      'Too long - 31 chars' => [str_repeat('a', 31), 'Username must be at most 30 characters long'],
      'Too long - 50 chars' => [str_repeat('x', 50), 'Username must be at most 30 characters long'],
    ];
  }

  /**
   * @return array<string, array{string}>
   */
  public static function provideValidUsernames(): array {
    return [
      'Minimum length' => ['abc'],
      'With numbers' => ['user123'],
      'With underscore' => ['test_user'],
      'With hyphen' => ['user-name'],
      'With dot' => ['user.name'],
      'Mixed case' => ['TestUser'],
      'Numbers only' => ['123'],
      'Maximum length' => [str_repeat('a', 30)],
      'Complex valid' => ['user_123.test-name'],
    ];
  }

  /**
   * @return array<string, array{string, int, string}>
   */
  public static function provideWithSuffixData(): array {
    return [
      'Appends suffix' => ['john', 1, 'john1'],
      'Truncates at maximum length' => [str_repeat('a', 30), 1, str_repeat('a', 29) . '1'],
      'Truncates for multi-digit suffix' => [str_repeat('a', 30), 10, str_repeat('a', 28) . '10'],
    ];
  }

  /**
   * @return array<string, array{string}>
   */
  public static function provideShortMultibyteValues(): array {
    return [
      'two cjk characters' => ['李明'],
      'two accented characters' => ['Ñø'],
      'single euro sign' => ['€'],
    ];
  }

  /**
   * @return array<string, array{string}>
   */
  public static function provideStoredUsernamesOutsideSignUpRules(): array {
    return [
      'Display name with space' => ['John Doe'],
      'Mixed case' => ['TestUser'],
      'Email shaped' => ['user@corp.com'],
      'Consecutive special characters' => ['john__doe'],
      'Reserved name' => ['Anonymous'],
    ];
  }

  #[Test]
  #[DataProvider('provideWithSuffixData')]
  public function itAppendsSuffixWithinMaximumLength(string $base, int $suffix, string $expected): void {
    $username = Username::fromString($base)->withSuffix($suffix);

    $this->assertSame($expected, $username->toString());
  }

  #[Test]
  public function itDoesNotSplitAMultibyteCharacterWhenTruncatingForSuffix(): void {
    $username = Username::fromString(str_repeat('a', 24) . '€bb')->withSuffix(1234);

    $this->assertTrue(mb_check_encoding($username->toString(), 'UTF-8'));
    $this->assertSame(str_repeat('a', 24) . '1234', $username->toString());
  }

  #[Test]
  public function itCreatesValidUsername(): void {
    $usernameString = 'testuser';
    $username = Username::fromString($usernameString);

    $this->assertInstanceOf(Username::class, $username);
    $this->assertSame($usernameString, $username->toString());
  }

  #[Test]
  #[DataProvider('provideValidUsernames')]
  public function itCreatesValidUsernamesFromDifferentFormats(string $usernameString): void {
    $username = Username::fromString($usernameString);

    $this->assertInstanceOf(Username::class, $username);
    $this->assertSame($usernameString, $username->toString());
  }

  #[Test]
  public function itHandlesCaseSensitivity(): void {
    $username1 = Username::fromString('TestUser');
    $username2 = Username::fromString('testuser');

    $this->assertNotSame($username1->toString(), $username2->toString());
    $this->assertSame('TestUser', $username1->toString());
  }

  #[Test]
  public function itHandlesUsernameEquality(): void {
    $username1 = Username::fromString('testuser');
    $username2 = Username::fromString('testuser');
    $username3 = Username::fromString('differentuser');

    $this->assertSame($username1->toString(), $username2->toString());
    $this->assertNotSame($username1->toString(), $username3->toString());
  }

  #[Test]
  #[DataProvider('provideInvalidUsernames')]
  public function itThrowsExceptionForInvalidUsername(string $invalidUsername, string $expectedMessage): void {
    $this->expectException(InvalidUsernameException::class);
    $this->expectExceptionMessage($expectedMessage);

    Username::fromString($invalidUsername);
  }

  #[Test]
  #[Group('sc-1142')]
  public function itSanitizesMaximumLengthUsernameWithSpecialCharacterToItsEscapedForm(): void {
    $sanitized = Username::fromString(str_repeat('a', 29) . '<')->sanitize();

    $this->assertInstanceOf(Username::class, $sanitized);
    $this->assertSame('aaaaaaaaaaaaaaaaaaaaaaaaaaaaa&lt;', $sanitized->toString());
  }

  #[Test]
  #[Group('sc-1142')]
  public function itSanitizesUsernameWithoutSpecialCharactersToAnEqualValue(): void {
    $sanitized = Username::fromString('user_123.test-name')->sanitize();

    $this->assertSame('user_123.test-name', $sanitized->toString());
  }

  #[Test]
  #[Group('sc-1142')]
  public function itKeepsStrictLengthCheckOnRawInputThatLooksEscaped(): void {
    $this->expectException(InvalidUsernameException::class);
    $this->expectExceptionMessage('Username must be at most 30 characters long');

    Username::fromString(str_repeat('a', 29) . '&lt;');
  }

  #[Test]
  #[Group('sc-1142')]
  #[DataProvider('provideShortMultibyteValues')]
  public function itSanitizesShortMultibyteUsernameToAnEqualValue(string $value): void {
    $sanitized = Username::fromString($value)->sanitize();

    $this->assertSame($value, $sanitized->toString());
  }

  #[Test]
  #[Group('sc-1143')]
  #[DataProvider('provideStoredUsernamesOutsideSignUpRules')]
  public function itKeepsAcceptingStoredUsernamesThatPredateSignUpRules(string $value): void {
    $this->assertSame($value, Username::fromString($value)->toString());
  }
}
