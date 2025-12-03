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

final class GetCommentsByImageHandlerTest extends TestCase {
  #[Test]
  public function itReturnsCommentsCollection(): void {
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $imageId = 'image-123';

    $paginator = $this->createMock(Paginator::class);
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

    $paginator = $this->createMock(Paginator::class);
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
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $imageId = 'image-123';
    $totalCount = 15;

    $paginator = $this->createMock(Paginator::class);
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
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $imageId = 'image-123';

    $comment1 = $this->createMock(CommentView::class);
    $comment2 = $this->createMock(CommentView::class);
    $comment3 = $this->createMock(CommentView::class);

    $paginator = $this->createMock(Paginator::class);
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
    $commentRepository = $this->createMock(CommentRepositoryInterface::class);
    $imageId = 'image-with-no-comments';

    $paginator = $this->createMock(Paginator::class);
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

    $paginator = $this->createMock(Paginator::class);
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
