<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Application\Query\GetCommentsByImage;

use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Application\Query\GetCommentsByImage\GetCommentsByImageHandler;
use Slink\Comment\Application\Query\GetCommentsByImage\GetCommentsByImageQuery;
use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class GetCommentsByImageHandlerTest extends TestCase {
  private function createMockCommentView(): CommentView {
    $userView = $this->createStub(UserView::class);
    $userView->method('getUuid')->willReturn('user-123');
    $userView->method('getDisplayName')->willReturn('Test User');
    
    $mock = $this->createStub(CommentView::class);
    $mock->method('getId')->willReturn('comment-123');
    $mock->method('getContent')->willReturn('Test content');
    $mock->method('getDisplayContent')->willReturn('Test content');
    $mock->method('getCreatedAt')->willReturn(DateTime::now());
    $mock->method('getUpdatedAt')->willReturn(null);
    $mock->method('getDeletedAt')->willReturn(null);
    $mock->method('isDeleted')->willReturn(false);
    $mock->method('isEdited')->willReturn(false);
    $mock->method('getUser')->willReturn($userView);
    $mock->method('getReferencedComment')->willReturn(null);
    $mock->method('getReferencedCommentSummary')->willReturn(null);
    
    return $mock;
  }

  #[Test]
  public function itReturnsCommentsCollection(): void {
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $imageId = 'image-123';

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));
    $paginator->method('count')->willReturn(0);

    $commentRepository->expects($this->once())
      ->method('findByImageId')
      ->with($imageId, 1, 20)
      ->willReturn($paginator);

    $handler = new GetCommentsByImageHandler($commentRepository);

    $query = new GetCommentsByImageQuery($imageId);

    $result = $handler($query);

    $this->assertInstanceOf(Collection::class, $result);
  }

  #[Test]
  public function itPassesPaginationParametersToRepository(): void {
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $imageId = 'image-123';
    $page = 2;
    $limit = 30;

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));
    $paginator->method('count')->willReturn(0);

    $commentRepository->expects($this->once())
      ->method('findByImageId')
      ->with($imageId, $page, $limit)
      ->willReturn($paginator);

    $handler = new GetCommentsByImageHandler($commentRepository);

    $query = new GetCommentsByImageQuery($imageId, $page, $limit);

    $handler($query);
  }

  #[Test]
  public function itReturnsCorrectTotalCount(): void {
    $commentRepository = $this->createStub(CommentRepositoryInterface::class);
    $imageId = 'image-123';
    $totalCount = 15;

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));
    $paginator->method('count')->willReturn($totalCount);

    $commentRepository->method('findByImageId')->willReturn($paginator);

    $handler = new GetCommentsByImageHandler($commentRepository);

    $query = new GetCommentsByImageQuery($imageId);

    $result = $handler($query);

    $this->assertEquals($totalCount, $result->total);
  }

  #[Test]
  public function itTransformsCommentsToItems(): void {
    $commentRepository = $this->createStub(CommentRepositoryInterface::class);
    $imageId = 'image-123';

    $comment1 = $this->createMockCommentView();
    $comment2 = $this->createMockCommentView();
    $comment3 = $this->createMockCommentView();

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator([$comment1, $comment2, $comment3]));
    $paginator->method('count')->willReturn(3);

    $commentRepository->method('findByImageId')->willReturn($paginator);

    $handler = new GetCommentsByImageHandler($commentRepository);

    $query = new GetCommentsByImageQuery($imageId);

    $result = $handler($query);

    $this->assertCount(3, $result->data);
  }

  #[Test]
  public function itReturnsEmptyCollectionWhenNoComments(): void {
    $commentRepository = $this->createStub(CommentRepositoryInterface::class);
    $imageId = 'image-with-no-comments';

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));
    $paginator->method('count')->willReturn(0);

    $commentRepository->method('findByImageId')->willReturn($paginator);

    $handler = new GetCommentsByImageHandler($commentRepository);

    $query = new GetCommentsByImageQuery($imageId);

    $result = $handler($query);

    $this->assertCount(0, $result->data);
    $this->assertEquals(0, $result->total);
  }

  #[Test]
  public function itPassesCorrectImageIdToRepository(): void {
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $imageId = 'specific-image-id-12345';

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));
    $paginator->method('count')->willReturn(0);

    $commentRepository->expects($this->once())
      ->method('findByImageId')
      ->with($imageId, $this->anything(), $this->anything())
      ->willReturn($paginator);

    $handler = new GetCommentsByImageHandler($commentRepository);

    $query = new GetCommentsByImageQuery($imageId);

    $handler($query);
  }
}
