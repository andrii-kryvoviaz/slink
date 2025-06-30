<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject\Auth;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Exception\InvalidPasswordException;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use function password_hash;
use function password_verify;

final class HashedPasswordTest extends TestCase {

  private const VALID_PASSWORD = 'mypassword123';
  private const WRONG_PASSWORD = 'wrongpassword';

  /**
   * @return array<string, array{string, string}>
   */
  public static function provideInvalidPasswords(): array {
    return [
      'Empty password' => ['', 'Min 6 characters password'],
      'Single character' => ['a', 'Min 6 characters password'],
      'Two characters' => ['ab', 'Min 6 characters password'],
      'Three characters' => ['abc', 'Min 6 characters password'],
      'Four characters' => ['abcd', 'Min 6 characters password'],
      'Five characters' => ['abcde', 'Min 6 characters password'],
    ];
  }

  /**
   * @return array<string, array{string}>
   */
  public static function provideValidPasswords(): array {
    return [
      'Minimum length' => ['abcdef'],
      'Alphanumeric' => ['password123'],
      'Mixed case' => ['MyPassword'],
      'With symbols' => ['P@ssw0rd!'],
      'Very long password' => ['very_long_password_that_should_work_fine_123'],
      'Only numbers' => ['123456'],
      'Mixed alphanumeric' => ['AbCdEf123'],
      'With spaces' => ['my password 123'],
      'Unicode characters' => ['пароль123'],
      'Special symbols' => ['test#$%^&*()'],
    ];
  }

  #[Test]
  public function itCreatesFromExistingHash(): void {
    $hash = password_hash('testpassword', PASSWORD_BCRYPT, ['cost' => 12]);
    $hashedPassword = HashedPassword::fromHash($hash);

    $this->assertInstanceOf(HashedPassword::class, $hashedPassword);
    $this->assertSame($hash, $hashedPassword->toString());
  }

  #[Test]
  public function itDoesNotMatchIncorrectPassword(): void {
    $plainPassword = self::VALID_PASSWORD;
    $wrongPassword = self::WRONG_PASSWORD;
    $hashedPassword = HashedPassword::encode($plainPassword);

    $this->assertFalse($hashedPassword->match($wrongPassword));
  }

  #[Test]
  public function itEncodesValidPassword(): void {
    $password = self::VALID_PASSWORD;
    $hashedPassword = HashedPassword::encode($password);

    $this->assertInstanceOf(HashedPassword::class, $hashedPassword);
    $this->assertNotSame($password, $hashedPassword->toString());
    $this->assertTrue(password_verify($password, $hashedPassword->toString()));
    $this->assertStringStartsWith('$2y$', $hashedPassword->toString());
  }

  #[Test]
  #[DataProvider('provideValidPasswords')]
  public function itEncodesValidPasswords(string $validPassword): void {
    $hashedPassword = HashedPassword::encode($validPassword);

    $this->assertInstanceOf(HashedPassword::class, $hashedPassword);
    $this->assertTrue($hashedPassword->match($validPassword));
  }

  #[Test]
  public function itHandlesCaseSensitivePasswords(): void {
    $password = 'MyPassword123';
    $hashedPassword = HashedPassword::encode($password);

    $this->assertTrue($hashedPassword->match('MyPassword123'));
    $this->assertFalse($hashedPassword->match('mypassword123'));
    $this->assertFalse($hashedPassword->match('MYPASSWORD123'));
  }

  #[Test]
  public function itHandlesEmptyStringInMatching(): void {
    $hashedPassword = HashedPassword::encode(self::VALID_PASSWORD);

    $this->assertFalse($hashedPassword->match(''));
  }

  #[Test]
  public function itHandlesSpecialCharacters(): void {
    $password = 'P@ssw0rd!#$%';
    $hashedPassword = HashedPassword::encode($password);

    $this->assertTrue($hashedPassword->match($password));
    $this->assertFalse($hashedPassword->match('P@ssw0rd!#$'));
  }

  #[Test]
  public function itHandlesUnicodeCharacters(): void {
    $password = 'pássw0rd123ñ';
    $hashedPassword = HashedPassword::encode($password);

    $this->assertTrue($hashedPassword->match($password));
    $this->assertFalse($hashedPassword->match('password123n'));
  }

  #[Test]
  public function itHandlesVeryLongPasswords(): void {
    $longPassword = str_repeat('a', 100) . '123!';
    $hashedPassword = HashedPassword::encode($longPassword);

    $this->assertTrue($hashedPassword->match($longPassword));
    $this->assertFalse($hashedPassword->match(str_repeat('b', 100) . '123!'));
  }

  #[Test]
  public function itMatchesCorrectPassword(): void {
    $plainPassword = self::VALID_PASSWORD;
    $hashedPassword = HashedPassword::encode($plainPassword);

    $this->assertTrue($hashedPassword->match($plainPassword));
  }

  #[Test]
  public function itProducesConsistentHashForSamePassword(): void {
    $password = self::VALID_PASSWORD;
    $hash1 = HashedPassword::encode($password);
    $hash2 = HashedPassword::encode($password);

    $this->assertNotSame($hash1->toString(), $hash2->toString());
    $this->assertTrue($hash1->match($password));
    $this->assertTrue($hash2->match($password));
  }

  #[Test]
  #[DataProvider('provideInvalidPasswords')]
  public function itThrowsExceptionForInvalidPassword(string $invalidPassword, string $expectedMessage): void {
    $this->expectException(InvalidPasswordException::class);
    $this->expectExceptionMessage($expectedMessage);

    HashedPassword::encode($invalidPassword);
  }
}
