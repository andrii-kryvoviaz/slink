<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Domain\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Tag\Domain\Exception\DuplicateTagException;

final class DuplicateTagExceptionTest extends TestCase {

  #[Test]
  public function itCreatesExceptionForRootTag(): void {
    $tagName = 'duplicate-root-tag';
    $exception = new DuplicateTagException($tagName);
    
    $expectedMessage = 'Root tag "duplicate-root-tag" already exists';
    $this->assertEquals($expectedMessage, $exception->getMessage());
  }

  #[Test]
  public function itCreatesExceptionForChildTag(): void {
    $tagName = 'duplicate-child-tag';
    $parentId = 'parent-123';
    $exception = new DuplicateTagException($tagName, $parentId);
    
    $expectedMessage = 'Tag "duplicate-child-tag" already exists under parent "parent-123"';
    $this->assertEquals($expectedMessage, $exception->getMessage());
  }

  #[Test]
  public function itCreatesExceptionForChildTagWithNullParent(): void {
    $tagName = 'another-tag';
    $exception = new DuplicateTagException($tagName, null);
    
    $expectedMessage = 'Root tag "another-tag" already exists';
    $this->assertEquals($expectedMessage, $exception->getMessage());
  }

  #[Test]
  public function itExtendsInvalidArgumentException(): void {
    $exception = new DuplicateTagException('test-tag');
    
    $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
  }

  #[Test]
  public function itHandlesSpecialCharactersInTagName(): void {
    $tagName = 'special_tag-123';
    $parentId = 'parent_456-abc';
    $exception = new DuplicateTagException($tagName, $parentId);
    
    $expectedMessage = 'Tag "special_tag-123" already exists under parent "parent_456-abc"';
    $this->assertEquals($expectedMessage, $exception->getMessage());
  }
}