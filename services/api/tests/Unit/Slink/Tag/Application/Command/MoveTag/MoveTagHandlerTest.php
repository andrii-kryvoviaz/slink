<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Application\Command\MoveTag;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Application\Command\MoveTag\MoveTagCommand;
use Slink\Tag\Application\Command\MoveTag\MoveTagHandler;
use Slink\Tag\Domain\Exception\InvalidTagMoveException;
use Slink\Tag\Domain\Exception\TagAccessDeniedException;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Specification\TagDuplicateSpecificationInterface;
use Slink\Tag\Domain\Tag;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;

final class MoveTagHandlerTest extends TestCase {

  #[Test]
  public function itMovesTagToNewParent(): void {
    $tagStore = $this->createMock(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);
    $tag = $this->createMock(Tag::class);
    $userId = ID::generate();
    $tagId = ID::generate();
    $newParentId = ID::generate();

    $tag->method('getUserId')->willReturn($userId);
    $tag->method('getPath')->willReturn(TagPath::fromString('#tag'));
    $tag->method('getName')->willReturn(TagName::fromString('tag'));

    $newParent = $this->createStub(Tag::class);
    $newParent->method('getPath')->willReturn(TagPath::fromString('#newparent'));

    $tagStore->method('get')->willReturnCallback(
      fn(ID $id) => match (true) {
        $id->equals($tagId) => $tag,
        $id->equals($newParentId) => $newParent,
        default => throw new \RuntimeException('Unexpected ID'),
      }
    );

    $tag->expects($this->once())->method('move');
    $tagStore->expects($this->once())->method('store')->with($tag);

    $handler = new MoveTagHandler($tagStore, $duplicateSpec);
    $command = new MoveTagCommand($tagId->toString(), $newParentId->toString());
    $handler($command, $userId->toString());
  }

  #[Test]
  public function itMovesTagToRoot(): void {
    $tagStore = $this->createMock(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);
    $tag = $this->createMock(Tag::class);
    $userId = ID::generate();
    $tagId = ID::generate();

    $tag->method('getUserId')->willReturn($userId);
    $tag->method('getPath')->willReturn(TagPath::fromString('#parent/tag'));
    $tag->method('getName')->willReturn(TagName::fromString('tag'));

    $tagStore->method('get')->willReturnCallback(
      fn(ID $id) => match (true) {
        $id->equals($tagId) => $tag,
        default => throw new \RuntimeException('Unexpected ID'),
      }
    );

    $tag->expects($this->once())
      ->method('move')
      ->with(
        $this->isNull(),
        $this->callback(fn(TagPath $path) => $path->getValue() === '#tag')
      );
    $tagStore->expects($this->once())->method('store')->with($tag);

    $handler = new MoveTagHandler($tagStore, $duplicateSpec);
    $command = new MoveTagCommand($tagId->toString(), null);
    $handler($command, $userId->toString());
  }

  #[Test]
  public function itThrowsWhenUserDoesNotOwnTag(): void {
    $tagStore = $this->createStub(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);
    $tag = $this->createStub(Tag::class);
    $tagOwnerId = ID::generate();
    $differentUserId = ID::generate();
    $tagId = ID::generate();
    $newParentId = ID::generate();

    $tag->method('getUserId')->willReturn($tagOwnerId);
    $tagStore->method('get')->willReturn($tag);

    $this->expectException(TagAccessDeniedException::class);

    $handler = new MoveTagHandler($tagStore, $duplicateSpec);
    $command = new MoveTagCommand($tagId->toString(), $newParentId->toString());
    $handler($command, $differentUserId->toString());
  }

  #[Test]
  public function itThrowsWhenMovingTagToItself(): void {
    $tagStore = $this->createStub(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);
    $tag = $this->createStub(Tag::class);
    $userId = ID::generate();
    $tagId = ID::generate();

    $tag->method('getUserId')->willReturn($userId);
    $tagStore->method('get')->willReturn($tag);

    $this->expectException(InvalidTagMoveException::class);
    $this->expectExceptionMessage('Cannot move a tag to itself');

    $handler = new MoveTagHandler($tagStore, $duplicateSpec);
    $command = new MoveTagCommand($tagId->toString(), $tagId->toString());
    $handler($command, $userId->toString());
  }

  #[Test]
  public function itThrowsWhenMovingToDescendant(): void {
    $tagStore = $this->createStub(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);
    $tag = $this->createStub(Tag::class);
    $userId = ID::generate();
    $tagId = ID::generate();
    $newParentId = ID::generate();

    $tag->method('getUserId')->willReturn($userId);
    $tag->method('getPath')->willReturn(TagPath::fromString('#parent'));

    $newParent = $this->createStub(Tag::class);
    $newParent->method('getPath')->willReturn(TagPath::fromString('#parent/child'));

    $tagStore->method('get')->willReturnCallback(
      fn(ID $id) => match (true) {
        $id->equals($tagId) => $tag,
        $id->equals($newParentId) => $newParent,
        default => throw new \RuntimeException('Unexpected ID'),
      }
    );

    $this->expectException(InvalidTagMoveException::class);
    $this->expectExceptionMessage('Cannot move a tag to one of its descendants');

    $handler = new MoveTagHandler($tagStore, $duplicateSpec);
    $command = new MoveTagCommand($tagId->toString(), $newParentId->toString());
    $handler($command, $userId->toString());
  }
}
