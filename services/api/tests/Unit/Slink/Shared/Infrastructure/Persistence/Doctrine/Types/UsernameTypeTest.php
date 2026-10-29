<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\Persistence\Doctrine\Types\UsernameType;
use Slink\User\Domain\ValueObject\Username;

final class UsernameTypeTest extends TestCase {

  /**
   * @return array<string, array{string}>
   */
  public static function provideStoredEscapedValuesInvalidOnceUnescaped(): array {
    return [
      'Too long - 31 chars unescaped' => [str_repeat('a', 30) . '&lt;'],
      'Too short - 2 chars unescaped' => ['&lt;&gt;'],
    ];
  }

  #[Test]
  #[Group('sc-1142')]
  public function itLoadsStoredEscapedMaximumLengthUsernameAsItsEscapedValue(): void {
    $type = new UsernameType();

    $username = $type->convertToPHPValue('aaaaaaaaaaaaaaaaaaaaaaaaaaaaa&lt;', $this->createStub(AbstractPlatform::class));

    $this->assertInstanceOf(Username::class, $username);
    $this->assertSame('aaaaaaaaaaaaaaaaaaaaaaaaaaaaa&lt;', $username->toString());
  }

  #[Test]
  #[Group('sc-1142')]
  public function itLoadsStoredShortMultibyteUsername(): void {
    $type = new UsernameType();

    $username = $type->convertToPHPValue('李明', $this->createStub(AbstractPlatform::class));

    $this->assertInstanceOf(Username::class, $username);
    $this->assertSame('李明', $username->toString());
  }

  #[Test]
  #[Group('sc-1142')]
  #[DataProvider('provideStoredEscapedValuesInvalidOnceUnescaped')]
  public function itRejectsStoredEscapedValueInvalidOnceUnescaped(string $storedValue): void {
    $this->expectException(\InvalidArgumentException::class);

    (new UsernameType())->convertToPHPValue($storedValue, $this->createStub(AbstractPlatform::class));
  }
}
