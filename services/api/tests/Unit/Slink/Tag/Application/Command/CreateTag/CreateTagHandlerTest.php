<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Application\Command\CreateTag;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Application\Command\CreateTag\CreateTagCommand;
use Slink\Tag\Application\Command\CreateTag\CreateTagHandler;
use Slink\Tag\Domain\Exception\DuplicateTagException;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Specification\TagDuplicateSpecificationInterface;
use Slink\Tag\Domain\Tag;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;

final class CreateTagHandlerTest extends TestCase {

  #[Test]
  public function itCreatesRootTagSuccessfully(): void {
    $tagStore = $this->createMock(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createMock(TagDuplicateSpecificationInterface::class);
    
    $duplicateSpec->expects($this->once())
      ->method('ensureUnique')
      ->with(
        $this->callback(fn($name) => $name->getValue() === 'test-tag'),
        $this->isInstanceOf(ID::class),
        $this->isNull()
      );

    $tagStore->expects($this->once())
      ->method('store')
      ->with($this->isInstanceOf(Tag::class));

    $handler = new CreateTagHandler($tagStore, $duplicateSpec);
    $command = new CreateTagCommand('test-tag');

    $tagId = $handler($command, 'user-123');

    $this->assertInstanceOf(ID::class, $tagId);
  }

  #[Test]
  public function itCreatesChildTagSuccessfully(): void {
    $tagStore = $this->createMock(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createMock(TagDuplicateSpecificationInterface::class);
    $parentTag = $this->createMock(Tag::class);
    $parentPath = TagPath::fromString('#parent');
    
    $parentId = ID::generate();
    
    $parentTag->method('getPath')
      ->willReturn($parentPath);

    $duplicateSpec->expects($this->once())
      ->method('ensureUnique')
      ->with(
        $this->callback(fn($name) => $name->getValue() === 'child-tag'),
        $this->isInstanceOf(ID::class),
        $this->equalTo($parentId)
      );

    $tagStore->expects($this->once())
      ->method('get')
      ->with($parentId)
      ->willReturn($parentTag);

    $tagStore->expects($this->once())
      ->method('store')
      ->with($this->isInstanceOf(Tag::class));

    $handler = new CreateTagHandler($tagStore, $duplicateSpec);
    $command = new CreateTagCommand('child-tag', $parentId->toString());

    $tagId = $handler($command, 'user-456');

    $this->assertInstanceOf(ID::class, $tagId);
  }

  #[Test]
  public function itThrowsExceptionWhenTagIsDuplicate(): void {
    $tagStore = $this->createMock(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createMock(TagDuplicateSpecificationInterface::class);
    
    $duplicateSpec->expects($this->once())
      ->method('ensureUnique')
      ->willThrowException(new DuplicateTagException('Tag already exists'));

    $handler = new CreateTagHandler($tagStore, $duplicateSpec);
    $command = new CreateTagCommand('duplicate-tag');

    $this->expectException(DuplicateTagException::class);
    $this->expectExceptionMessage('Tag already exists');

    $handler($command, 'user-789');
  }

  #[Test]
  public function itHandlesTagNameCorrectly(): void {
    $tagStore = $this->createMock(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createMock(TagDuplicateSpecificationInterface::class);
    
    $duplicateSpec->expects($this->once())
      ->method('ensureUnique')
      ->with(
        $this->callback(function (TagName $name) {
          return $name->getValue() === 'special-chars_123';
        }),
        $this->isInstanceOf(ID::class),
        $this->isNull()
      );

    $tagStore->method('store');

    $handler = new CreateTagHandler($tagStore, $duplicateSpec);
    $command = new CreateTagCommand('special-chars_123');

    $tagId = $handler($command, 'user-special');

    $this->assertInstanceOf(ID::class, $tagId);
  }

  #[Test]
  public function itHandlesUserIdCorrectly(): void {
    $tagStore = $this->createMock(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createMock(TagDuplicateSpecificationInterface::class);
    
    $userIdString = 'specific-user-id-123';
    
    $duplicateSpec->expects($this->once())
      ->method('ensureUnique')
      ->with(
        $this->isInstanceOf(TagName::class),
        $this->callback(function (ID $userId) use ($userIdString) {
          return $userId->toString() === $userIdString;
        }),
        $this->isNull()
      );

    $tagStore->method('store');

    $handler = new CreateTagHandler($tagStore, $duplicateSpec);
    $command = new CreateTagCommand('user-test');

    $tagId = $handler($command, $userIdString);

    $this->assertInstanceOf(ID::class, $tagId);
  }

  #[Test]
  public function itReturnsGeneratedTagId(): void {
    $tagStore = $this->createMock(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createMock(TagDuplicateSpecificationInterface::class);
    
    $duplicateSpec->method('ensureUnique');
    $tagStore->method('store');

    $handler = new CreateTagHandler($tagStore, $duplicateSpec);
    $command = new CreateTagCommand('id-test');

    $tagId1 = $handler($command, 'user-1');
    $tagId2 = $handler($command, 'user-2');

    $this->assertInstanceOf(ID::class, $tagId1);
    $this->assertInstanceOf(ID::class, $tagId2);
    $this->assertNotEquals($tagId1->toString(), $tagId2->toString());
  }
}