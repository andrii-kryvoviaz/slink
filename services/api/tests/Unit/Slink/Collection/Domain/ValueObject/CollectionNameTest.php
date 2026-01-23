<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Domain\Exception\InvalidArgumentException;

final class CollectionNameTest extends TestCase {
  #[Test]
  public function itCreatesFromString(): void {
    $name = CollectionName::fromString('My Collection');
    
    $this->assertEquals('My Collection', $name->toString());
  }

  #[Test]
  public function itThrowsExceptionForEmptyName(): void {
    $this->expectException(InvalidArgumentException::class);
    
    CollectionName::fromString('');
  }

  #[Test]
  public function itThrowsExceptionForWhitespaceName(): void {
    $this->expectException(InvalidArgumentException::class);
    
    CollectionName::fromString('   ');
  }

  #[Test]
  public function itTrimsWhitespace(): void {
    $name = CollectionName::fromString('  My Collection  ');
    
    $this->assertEquals('My Collection', $name->toString());
  }

  #[Test]
  public function itAllowsMaximumLength(): void {
    $longName = str_repeat('a', 100);
    $name = CollectionName::fromString($longName);
    
    $this->assertEquals($longName, $name->toString());
  }

  #[Test]
  public function itThrowsExceptionForTooLongName(): void {
    $this->expectException(InvalidArgumentException::class);
    
    $tooLongName = str_repeat('a', 101);
    CollectionName::fromString($tooLongName);
  }

  #[Test]
  public function itChecksEquality(): void {
    $name1 = CollectionName::fromString('My Collection');
    $name2 = CollectionName::fromString('My Collection');
    $name3 = CollectionName::fromString('Other Collection');
    
    $this->assertTrue($name1->equals($name2));
    $this->assertFalse($name1->equals($name3));
  }
}
