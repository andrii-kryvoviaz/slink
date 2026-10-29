<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\Persistence\Doctrine\Types\DisplayNameType;
use Slink\User\Domain\ValueObject\DisplayName;

final class DisplayNameTypeTest extends TestCase {

  /**
   * @return array<string, array{string}>
   */
  public static function provideStoredEscapedValuesInvalidOnceUnescaped(): array {
    return [
      'Too long - 31 chars unescaped' => [str_repeat('a', 30) . '&apos;'],
      'Too short - 2 chars unescaped' => ['&apos;&apos;'],
    ];
  }

  #[Test]
  #[Group('sc-1142')]
  public function itLoadsStoredEscapedMaximumLengthDisplayNameAsItsEscapedValue(): void {
    $type = new DisplayNameType();

    $displayName = $type->convertToPHPValue('Siobhan O&apos;Callaghan-Fitzgerald', $this->createStub(AbstractPlatform::class));

    $this->assertInstanceOf(DisplayName::class, $displayName);
    $this->assertSame('Siobhan O&apos;Callaghan-Fitzgerald', $displayName->toString());
  }

  #[Test]
  #[Group('sc-1142')]
  public function itKeepsLoadingNullDisplayNameAsGuest(): void {
    $type = new DisplayNameType();

    $displayName = $type->convertToPHPValue(null, $this->createStub(AbstractPlatform::class));

    $this->assertSame('Guest', $displayName->toString());
  }

  #[Test]
  #[Group('sc-1142')]
  public function itLoadsStoredShortMultibyteDisplayName(): void {
    $type = new DisplayNameType();

    $displayName = $type->convertToPHPValue('李明', $this->createStub(AbstractPlatform::class));

    $this->assertSame('李明', $displayName->toString());
  }

  #[Test]
  #[Group('sc-1142')]
  #[DataProvider('provideStoredEscapedValuesInvalidOnceUnescaped')]
  public function itRejectsStoredEscapedValueInvalidOnceUnescaped(string $storedValue): void {
    $this->expectException(\InvalidArgumentException::class);

    (new DisplayNameType())->convertToPHPValue($storedValue, $this->createStub(AbstractPlatform::class));
  }
}
