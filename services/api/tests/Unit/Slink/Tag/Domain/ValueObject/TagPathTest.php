<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;

final class TagPathTest extends TestCase {

  #[Test]
  public function itCreatesRootPath(): void {
    $tagName = TagName::fromString('root-tag');
    $tagPath = TagPath::createRoot($tagName);
    
    $this->assertEquals('#root-tag', $tagPath->getValue());
    $this->assertEquals(1, $tagPath->getDepth());
    $this->assertFalse($tagPath->isChild());
    $this->assertNull($tagPath->getParentPath());
    $this->assertEquals('root-tag', $tagPath->getTagName());
  }

  #[Test]
  public function itCreatesChildPath(): void {
    $parentPath = TagPath::fromString('#parent');
    $childName = TagName::fromString('child');
    $childPath = TagPath::createChild($parentPath, $childName);
    
    $this->assertEquals('#parent/child', $childPath->getValue());
    $this->assertEquals(2, $childPath->getDepth());
    $this->assertTrue($childPath->isChild());
    $this->assertEquals('child', $childPath->getTagName());
  }

  #[Test]
  public function itCreatesDeepChildPath(): void {
    $parentPath = TagPath::fromString('#parent/middle');
    $childName = TagName::fromString('child');
    $childPath = TagPath::createChild($parentPath, $childName);
    
    $this->assertEquals('#parent/middle/child', $childPath->getValue());
    $this->assertEquals(3, $childPath->getDepth());
    $this->assertTrue($childPath->isChild());
  }

  #[Test]
  public function itGetsParentPathCorrectly(): void {
    $childPath = TagPath::fromString('#parent/child');
    $parentPath = $childPath->getParentPath();
    
    $this->assertNotNull($parentPath);
    $this->assertEquals('#parent', $parentPath->getValue());
  }

  #[Test]
  public function itGetsParentPathForDeepNesting(): void {
    $deepPath = TagPath::fromString('#grandparent/parent/child');
    $parentPath = $deepPath->getParentPath();
    
    $this->assertNotNull($parentPath);
    $this->assertEquals('#grandparent/parent', $parentPath->getValue());
  }

  #[Test]
  public function itReturnsNullParentForRootPath(): void {
    $rootPath = TagPath::fromString('#root');
    $parentPath = $rootPath->getParentPath();
    
    $this->assertNull($parentPath);
  }

  #[Test]
  public function itExtractsTagNameFromPath(): void {
    $simplePath = TagPath::fromString('#simple-tag');
    $this->assertEquals('simple-tag', $simplePath->getTagName());
    
    $nestedPath = TagPath::fromString('#parent/child/grandchild');
    $this->assertEquals('grandchild', $nestedPath->getTagName());
  }

  #[Test]
  public function itCalculatesDepthCorrectly(): void {
    $this->assertEquals(1, TagPath::fromString('#root')->getDepth());
    $this->assertEquals(2, TagPath::fromString('#parent/child')->getDepth());
    $this->assertEquals(3, TagPath::fromString('#grand/parent/child')->getDepth());
    $this->assertEquals(1, TagPath::fromString('#')->getDepth());
  }

  #[Test]
  public function itIdentifiesChildPaths(): void {
    $this->assertFalse(TagPath::fromString('#root')->isChild());
    $this->assertTrue(TagPath::fromString('#parent/child')->isChild());
    $this->assertTrue(TagPath::fromString('#grand/parent/child')->isChild());
  }

  #[Test]
  public function itCreatesFromString(): void {
    $path = TagPath::fromString('#custom/path');
    
    $this->assertEquals('#custom/path', $path->getValue());
  }

  #[Test]
  public function itCreatesFromPayloadWithValue(): void {
    $payload = ['value' => '#payload/path'];
    $path = TagPath::fromPayload($payload);
    
    $this->assertEquals('#payload/path', $path->getValue());
  }

  #[Test]
  public function itCreatesFromPayloadWithPath(): void {
    $payload = ['path' => '#payload/path/alt'];
    $path = TagPath::fromPayload($payload);
    
    $this->assertEquals('#payload/path/alt', $path->getValue());
  }

  #[Test]
  public function itConvertsToPayload(): void {
    $path = TagPath::fromString('#test/path');
    $payload = $path->toPayload();
    
    $this->assertEquals(['value' => '#test/path'], $payload);
  }

  #[Test]
  public function itImplementsToString(): void {
    $path = TagPath::fromString('#string/path');
    
    $this->assertEquals('{"value":"#string\\/path"}', $path->toString());
  }
}