<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Application\Command\DeleteTag;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Application\Command\DeleteTag\DeleteTagCommand;
use Slink\Tag\Application\Command\DeleteTag\DeleteTagHandler;
use Slink\Tag\Domain\Exception\TagAccessDeniedException;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Tag;

final class DeleteTagHandlerTest extends TestCase {

  #[Test]
  public function itDeletesTagSuccessfully(): void {
    $tagStore = $this->createMock(TagStoreRepositoryInterface::class);
    $tagRepository = $this->createStub(TagRepositoryInterface::class);
    $tag = $this->createMock(Tag::class);
    $userId = ID::generate();
    $tagId = ID::generate();

    $tag->method('getUserId')->willReturn($userId);
    $tagStore->method('get')->willReturn($tag);
    $tagRepository->method('findChildIds')->willReturn(['child-1']);

    $tag->expects($this->once())->method('delete')->with(['child-1']);
    $tagStore->expects($this->once())->method('store')->with($tag);

    $handler = new DeleteTagHandler($tagStore, $tagRepository);
    $command = new DeleteTagCommand($tagId->toString());
    $handler($command, $userId->toString());
  }

  #[Test]
  public function itDeletesTagWithNoChildren(): void {
    $tagStore = $this->createMock(TagStoreRepositoryInterface::class);
    $tagRepository = $this->createStub(TagRepositoryInterface::class);
    $tag = $this->createMock(Tag::class);
    $userId = ID::generate();
    $tagId = ID::generate();

    $tag->method('getUserId')->willReturn($userId);
    $tagStore->method('get')->willReturn($tag);
    $tagRepository->method('findChildIds')->willReturn([]);

    $tag->expects($this->once())->method('delete')->with([]);
    $tagStore->expects($this->once())->method('store')->with($tag);

    $handler = new DeleteTagHandler($tagStore, $tagRepository);
    $command = new DeleteTagCommand($tagId->toString());
    $handler($command, $userId->toString());
  }

  #[Test]
  public function itThrowsExceptionWhenUserDoesNotOwnTag(): void {
    $tagStore = $this->createStub(TagStoreRepositoryInterface::class);
    $tagRepository = $this->createStub(TagRepositoryInterface::class);
    $tag = $this->createStub(Tag::class);
    $tagOwnerId = ID::generate();
    $differentUserId = ID::generate();
    $tagId = ID::generate();

    $tag->method('getUserId')->willReturn($tagOwnerId);
    $tagStore->method('get')->willReturn($tag);

    $this->expectException(TagAccessDeniedException::class);

    $handler = new DeleteTagHandler($tagStore, $tagRepository);
    $command = new DeleteTagCommand($tagId->toString());
    $handler($command, $differentUserId->toString());
  }
}
