<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Exception\InvalidUsernameException;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Username;

final class UsernameTest extends TestCase {

  /**
   * @return array<string, array{string, string}>
   */
  public static function provideDisplayNameToUsernameData(): array {
    return [
      'Simple name' => ['John Doe', 'john.doe'],
      'Multiple spaces' => ['  John   Doe  ', 'john...doe'],
      'With hyphen' => ['Jane Smith-Brown', 'jane.smith-brown'],
      'Special characters' => ['José María', 'josé.maría'],
      'Single name' => ['Madonna', 'madonna'],
      'Numbers in name' => ['User 123', 'user.123'],
      'Mixed case' => ['CamelCase Name', 'camelcase.name'],
    ];
  }

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

  #[Test]
  #[DataProvider('provideDisplayNameToUsernameData')]
  public function itCreatesUsernameFromDisplayName(string $displayNameString, string $expectedUsername): void {
    $displayName = DisplayName::fromString($displayNameString);
    $username = Username::fromDisplayName($displayName);

    $this->assertInstanceOf(Username::class, $username);
    $this->assertSame($expectedUsername, $username->toString());
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
}
