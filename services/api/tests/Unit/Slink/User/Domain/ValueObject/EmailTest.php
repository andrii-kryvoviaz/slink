<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Exception\InvalidEmailException;
use Slink\User\Domain\ValueObject\Email;

final class EmailTest extends TestCase {

  /**
   * @return array<string, array{string, string}>
   */
  public static function provideInvalidEmails(): array {
    return [
      'Empty string' => ['', 'Invalid email address.'],
      'No at symbol' => ['invalid', 'Invalid email address.'],
      'Only at symbol' => ['@domain.com', 'Invalid email address.'],
      'Missing domain' => ['user@', 'Invalid email address.'],
      'Double at symbols' => ['user@@domain.com', 'Invalid email address.'],
      'Missing TLD' => ['user@domain', 'Invalid email address.'],
      'Space in email' => ['user domain@example.com', 'Invalid email address.'],
      'Starting with dot' => ['.user@domain.com', 'Invalid email address.'],
      'Ending with dot' => ['user.@domain.com', 'Invalid email address.'],
      'Domain starting with dot' => ['user@.domain.com', 'Invalid email address.'],
      'Double dots in domain' => ['user@domain..com', 'Invalid email address.'],
      'Too long local part' => [str_repeat('a', 65) . '@example.com', 'Invalid email address.'],
      'Invalid characters' => ['user@dom$ain.com', 'Invalid email address.'],
    ];
  }

  /**
   * @return array<string, array{string}>
   */
  public static function provideValidEmails(): array {
    return [
      'Standard email' => ['user@domain.com'],
      'Email with dot in name' => ['user.name@domain.com'],
      'Email with plus tag' => ['user+tag@domain.com'],
      'Email with numbers' => ['user123@domain123.com'],
      'Email with underscore' => ['user_name@sub.domain.com'],
      'Short email' => ['a@b.co'],
      'Complex email' => ['test.email.with+symbol@example.com'],
      'Email with hyphen' => ['test-user@test-domain.com'],
      'Email with multiple dots' => ['first.middle.last@example.org'],
    ];
  }

  #[Test]
  public function itCreatesValidEmail(): void {
    $emailString = 'test@example.com';
    $email = Email::fromString($emailString);

    $this->assertInstanceOf(Email::class, $email);
    $this->assertSame($emailString, $email->toString());
  }

  #[Test]
  #[DataProvider('provideValidEmails')]
  public function itCreatesValidEmailsFromDifferentFormats(string $emailString): void {
    $email = Email::fromString($emailString);

    $this->assertInstanceOf(Email::class, $email);
    $this->assertSame($emailString, $email->toString());
  }

  #[Test]
  public function itHandlesCaseSensitivity(): void {
    $email1 = Email::fromString('Test@Example.Com');
    $email2 = Email::fromString('test@example.com');

    $this->assertNotSame($email1->toString(), $email2->toString());
    $this->assertSame('Test@Example.Com', $email1->toString());
  }

  #[Test]
  public function itHandlesEmailEquality(): void {
    $email1 = Email::fromString('test@example.com');
    $email2 = Email::fromString('test@example.com');
    $email3 = Email::fromString('different@example.com');

    $this->assertSame($email1->toString(), $email2->toString());
    $this->assertNotSame($email1->toString(), $email3->toString());
  }

  #[Test]
  public function itReturnsNullForInvalidEmailFromStringOrNull(): void {
    $email = Email::fromStringOrNull('invalid-email');

    $this->assertNull($email);
  }

  #[Test]
  public function itReturnsNullForNullEmail(): void {
    $email = Email::fromStringOrNull(null);

    $this->assertNull($email);
  }

  #[Test]
  public function itReturnsValidEmailFromStringOrNull(): void {
    $emailString = 'test@example.com';
    $email = Email::fromStringOrNull($emailString);

    $this->assertInstanceOf(Email::class, $email);
    $this->assertSame($emailString, $email->toString());
  }

  #[Test]
  #[DataProvider('provideInvalidEmails')]
  public function itThrowsExceptionForInvalidEmail(string $invalidEmail, string $expectedMessage): void {
    $this->expectException(InvalidEmailException::class);
    $this->expectExceptionMessage($expectedMessage);

    Email::fromString($invalidEmail);
  }
}
