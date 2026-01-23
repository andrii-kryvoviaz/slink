<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\ValueObject\CollectionDescription;

final class CollectionDescriptionTest extends TestCase {
  #[Test]
  public function itCreatesFromString(): void {
    $description = CollectionDescription::fromString('My description');
    
    $this->assertEquals('My description', $description->toString());
  }

  #[Test]
  public function itAllowsEmptyDescription(): void {
    $description = CollectionDescription::fromString('');
    
    $this->assertEquals('', $description->toString());
  }

  #[Test]
  public function itTrimsWhitespace(): void {
    $description = CollectionDescription::fromString('  My description  ');
    
    $this->assertEquals('My description', $description->toString());
  }

  #[Test]
  public function itAllowsMaximumLength(): void {
    $longDescription = str_repeat('a', 500);
    $description = CollectionDescription::fromString($longDescription);
    
    $this->assertEquals($longDescription, $description->toString());
  }

  #[Test]
  public function itChecksEquality(): void {
    $desc1 = CollectionDescription::fromString('My description');
    $desc2 = CollectionDescription::fromString('My description');
    $desc3 = CollectionDescription::fromString('Other description');
    
    $this->assertTrue($desc1->equals($desc2));
    $this->assertFalse($desc1->equals($desc3));
  }

  #[Test]
  public function itHandlesMultilineText(): void {
    $multiline = "Line 1\nLine 2\nLine 3";
    $description = CollectionDescription::fromString($multiline);
    
    $this->assertEquals($multiline, $description->toString());
  }
}
