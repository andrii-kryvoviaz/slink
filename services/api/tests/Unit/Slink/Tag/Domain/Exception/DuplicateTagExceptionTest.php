<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Domain\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Exception\SpecificationException;
use Slink\Tag\Domain\Exception\DuplicateTagException;

final class DuplicateTagExceptionTest extends TestCase {

  #[Test]
  public function itCreatesExceptionForRootTag(): void {
    $tagName = 'duplicate-root-tag';
    $exception = new DuplicateTagException($tagName);
    
    $expectedMessage = 'Tag "duplicate-root-tag" already exists';
    $this->assertEquals($expectedMessage, $exception->getMessage());
  }

  #[Test]
  public function itCreatesExceptionForChildTag(): void {
    $tagName = 'duplicate-child-tag';
    $parentId = 'parent-123';
    $exception = new DuplicateTagException($tagName, $parentId);
    
    $expectedMessage = 'Tag "duplicate-child-tag" already exists under this parent';
    $this->assertEquals($expectedMessage, $exception->getMessage());
  }

  #[Test]
  public function itCreatesExceptionForChildTagWithNullParent(): void {
    $tagName = 'another-tag';
    $exception = new DuplicateTagException($tagName, null);
    
    $expectedMessage = 'Tag "another-tag" already exists';
    $this->assertEquals($expectedMessage, $exception->getMessage());
  }

  #[Test]
  public function itExtendsSpecificationException(): void {
    $exception = new DuplicateTagException('test-tag');
    
    $this->assertInstanceOf(SpecificationException::class, $exception);
  }

  #[Test]
  public function itReturnsNameAsProperty(): void {
    $exception = new DuplicateTagException('test-tag');
    
    $this->assertEquals('name', $exception->getProperty());
  }

  #[Test]
  public function itHandlesSpecialCharactersInTagName(): void {
    $tagName = 'special_tag-123';
    $parentId = 'parent_456-abc';
    $exception = new DuplicateTagException($tagName, $parentId);
    
    $expectedMessage = 'Tag "special_tag-123" already exists under this parent';
    $this->assertEquals($expectedMessage, $exception->getMessage());
  }
}