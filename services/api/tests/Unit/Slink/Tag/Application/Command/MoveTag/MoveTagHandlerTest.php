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
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Specification\TagCircularMoveSpecificationInterface;
use Slink\Tag\Domain\Specification\TagDuplicateSpecificationInterface;
use Slink\Tag\Domain\Tag;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;

final class MoveTagHandlerTest extends TestCase {

  #[Test]
  public function itMovesTagToNewParent(): void {
    $tagStore = $this->createMock(TagStoreRepositoryInterface::class);
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);
    $circularMoveSpec = $this->createStub(TagCircularMoveSpecificationInterface::class);
    $tag = $this->createMock(Tag::class);
    $parentTag = $this->createStub(TagView::class);
    $userId = ID::generate();
    $tagId = ID::generate();
    $newParentId = ID::generate();

    $tag->method('getUserId')->willReturn($userId);
    $tag->method('getName')->willReturn(TagName::fromString('tag'));
    $parentTag->method('getPath')->willReturn('#1/2');

    $tagStore->method('get')->willReturn($tag);
    $tagRepository->expects($this->once())
      ->method('oneById')
      ->with($newParentId)
      ->willReturn($parentTag);

    $tag->expects($this->once())
      ->method('move')
      ->with(
        $newParentId,
        $this->callback(fn(TagPath $path) => $path->getValue() === '#1/2/tag')
      );
    $tagStore->expects($this->once())->method('store')->with($tag);

    $handler = new MoveTagHandler($tagStore, $duplicateSpec, $circularMoveSpec, $tagRepository);
    $command = new MoveTagCommand($tagId->toString(), $newParentId->toString());
    $handler($command, $userId->toString());
  }

  #[Test]
  public function itMovesTagToRoot(): void {
    $tagStore = $this->createMock(TagStoreRepositoryInterface::class);
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);
    $circularMoveSpec = $this->createStub(TagCircularMoveSpecificationInterface::class);
    $tag = $this->createMock(Tag::class);
    $userId = ID::generate();
    $tagId = ID::generate();

    $tag->method('getUserId')->willReturn($userId);
    $tag->method('getName')->willReturn(TagName::fromString('tag'));

    $tagStore->method('get')->willReturn($tag);
    $tagRepository->expects($this->never())->method('oneById');

    $tag->expects($this->once())
      ->method('move')
      ->with(
        $this->isNull(),
        $this->callback(fn(TagPath $path) => $path->getValue() === '#tag')
      );
    $tagStore->expects($this->once())->method('store')->with($tag);

    $handler = new MoveTagHandler($tagStore, $duplicateSpec, $circularMoveSpec, $tagRepository);
    $command = new MoveTagCommand($tagId->toString(), null);
    $handler($command, $userId->toString());
  }

  #[Test]
  public function itThrowsWhenUserDoesNotOwnTag(): void {
    $tagStore = $this->createStub(TagStoreRepositoryInterface::class);
    $tagRepository = $this->createStub(TagRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);
    $circularMoveSpec = $this->createStub(TagCircularMoveSpecificationInterface::class);
    $tag = $this->createStub(Tag::class);
    $tagOwnerId = ID::generate();
    $differentUserId = ID::generate();
    $tagId = ID::generate();
    $newParentId = ID::generate();

    $tag->method('getUserId')->willReturn($tagOwnerId);
    $tagStore->method('get')->willReturn($tag);

    $this->expectException(TagAccessDeniedException::class);

    $handler = new MoveTagHandler($tagStore, $duplicateSpec, $circularMoveSpec, $tagRepository);
    $command = new MoveTagCommand($tagId->toString(), $newParentId->toString());
    $handler($command, $differentUserId->toString());
  }

  #[Test]
  public function itThrowsWhenMovingTagToItself(): void {
    $tagStore = $this->createStub(TagStoreRepositoryInterface::class);
    $tagRepository = $this->createStub(TagRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);
    $circularMoveSpec = $this->createStub(TagCircularMoveSpecificationInterface::class);
    $tag = $this->createStub(Tag::class);
    $userId = ID::generate();
    $tagId = ID::generate();

    $tag->method('getUserId')->willReturn($userId);
    $tagStore->method('get')->willReturn($tag);

    $this->expectException(InvalidTagMoveException::class);
    $this->expectExceptionMessage('Cannot move a tag to itself');

    $handler = new MoveTagHandler($tagStore, $duplicateSpec, $circularMoveSpec, $tagRepository);
    $command = new MoveTagCommand($tagId->toString(), $tagId->toString());
    $handler($command, $userId->toString());
  }

  #[Test]
  public function itThrowsWhenMovingToDescendant(): void {
    $tagStore = $this->createStub(TagStoreRepositoryInterface::class);
    $tagRepository = $this->createStub(TagRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);
    $circularMoveSpec = $this->createMock(TagCircularMoveSpecificationInterface::class);
    $tag = $this->createStub(Tag::class);
    $userId = ID::generate();
    $tagId = ID::generate();
    $newParentId = ID::generate();

    $tag->method('getUserId')->willReturn($userId);
    $tagStore->method('get')->willReturn($tag);

    $circularMoveSpec->expects($this->once())
      ->method('validate')
      ->willThrowException(new InvalidTagMoveException('Cannot move a tag to one of its descendants'));

    $this->expectException(InvalidTagMoveException::class);
    $this->expectExceptionMessage('Cannot move a tag to one of its descendants');

    $handler = new MoveTagHandler($tagStore, $duplicateSpec, $circularMoveSpec, $tagRepository);
    $command = new MoveTagCommand($tagId->toString(), $newParentId->toString());
    $handler($command, $userId->toString());
  }
}
