<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Domain\ValueObject;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\User\Domain\Exception\InvalidPasswordException;

final class HashedSharePasswordTest extends TestCase {

  private const VALID_PASSWORD = 'mypassword123';
  private const WRONG_PASSWORD = 'wrongpassword';

  /**
   * @return array<string, array{string, string}>
   */
  public static function provideInvalidPasswords(): array {
    return [
      'Empty password' => ['', 'Min 6 characters password'],
      'Single character' => ['a', 'Min 6 characters password'],
      'Five characters' => ['abcde', 'Min 6 characters password'],
    ];
  }

  #[Test]
  public function itEncodesValidPasswordAndProducesBcryptHash(): void {
    $hashed = HashedSharePassword::encode(self::VALID_PASSWORD);

    $this->assertNotSame(self::VALID_PASSWORD, $hashed->toString());
    $this->assertStringStartsWith('$2y$', $hashed->toString());
  }

  #[Test]
  public function itMatchesEncodedPassword(): void {
    $hashed = HashedSharePassword::encode(self::VALID_PASSWORD);

    $this->assertTrue($hashed->match(self::VALID_PASSWORD));
  }

  #[Test]
  public function itDoesNotMatchIncorrectPassword(): void {
    $hashed = HashedSharePassword::encode(self::VALID_PASSWORD);

    $this->assertFalse($hashed->match(self::WRONG_PASSWORD));
  }

  #[Test]
  public function itRehydratesFromHashAndMatches(): void {
    $original = HashedSharePassword::encode(self::VALID_PASSWORD);
    $rehydrated = HashedSharePassword::fromHash($original->toString());

    $this->assertSame($original->toString(), $rehydrated->toString());
    $this->assertTrue($rehydrated->match(self::VALID_PASSWORD));
  }

  #[Test]
  public function itConsidersIdenticalHashesEqual(): void {
    $hashed = HashedSharePassword::encode(self::VALID_PASSWORD);
    $other = HashedSharePassword::fromHash($hashed->toString());

    $this->assertTrue($hashed->equals($other));
  }

  #[Test]
  public function itConsidersDifferentHashesNotEqual(): void {
    $a = HashedSharePassword::encode(self::VALID_PASSWORD);
    $b = HashedSharePassword::encode(self::VALID_PASSWORD);

    $this->assertFalse($a->equals($b));
  }

  #[Test]
  public function itConsidersNullNotEqual(): void {
    $hashed = HashedSharePassword::encode(self::VALID_PASSWORD);

    $this->assertFalse($hashed->equals(null));
  }

  #[Test]
  #[DataProvider('provideInvalidPasswords')]
  public function itThrowsForInvalidPasswords(string $invalid, string $expectedMessage): void {
    $this->expectException(InvalidPasswordException::class);
    $this->expectExceptionMessage($expectedMessage);

    HashedSharePassword::encode($invalid);
  }
}
