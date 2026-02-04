<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Application\Query\GetTagList;

use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Application\Query\GetTagList\GetTagListHandler;
use Slink\Tag\Application\Query\GetTagList\GetTagListQuery;
use Slink\Tag\Domain\Filter\TagListFilter;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;

final class GetTagListHandlerTest extends TestCase {

  #[Test]
  public function itReturnsTagListByIds(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $tagView1 = $this->createStub(TagView::class);
    $tagView2 = $this->createStub(TagView::class);
    $tagViews = [$tagView1, $tagView2];
    
    $userIdString = 'user-123';
    $tagIds = ['tag-1', 'tag-2'];

    $tagRepository->expects($this->once())
      ->method('findExactTagsByIds')
      ->with(
        $tagIds,
        $this->callback(fn($userId) => $userId->toString() === $userIdString)
      )
      ->willReturn($tagViews);

    $handler = new GetTagListHandler($tagRepository);
    $query = new GetTagListQuery(ids: $tagIds);

    $result = $handler($query, $userIdString);

    $this->assertInstanceOf(Collection::class, $result);
    $this->assertEquals(1, $result->page);
    $this->assertEquals(2, $result->total);
    $this->assertEquals(2, $result->limit);
    $this->assertCount(2, $result->data);
  }

  #[Test]
  public function itReturnsTagListWithPagination(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $tagView = $this->createStub(TagView::class);
    $tagViews = [$tagView];
    
    $userIdString = 'user-456';
    $page = 2;
    $limit = 10;

    $paginator = $this->createStub(\Doctrine\ORM\Tools\Pagination\Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator($tagViews));
    $paginator->method('count')->willReturn(25);
    
    $tagRepository->expects($this->once())
      ->method('getAllByPage')
      ->with(
        $page,
        $this->callback(function (TagListFilter $filter) use ($limit, $userIdString) {
          return $filter->getLimit() === $limit && $filter->getUserId() === $userIdString;
        })
      )
      ->willReturn($paginator);



    $handler = new GetTagListHandler($tagRepository);
    $query = new GetTagListQuery(limit: $limit, page: $page);

    $result = $handler($query, $userIdString);

    $this->assertInstanceOf(Collection::class, $result);
    $this->assertEquals($page, $result->page);
    $this->assertEquals(25, $result->total);
    $this->assertEquals($limit, $result->limit);
  }

  #[Test]
  public function itAppliesFiltersCorrectly(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $tagViews = [];
    
    $userIdString = 'user-789';
    $parentId = 'parent-123';
    $searchTerm = 'search-term';

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator($tagViews));
    $paginator->method('count')->willReturn(0);
    
    $tagRepository->expects($this->once())
      ->method('getAllByPage')
      ->with(
        1,
        $this->callback(function (TagListFilter $filter) use ($parentId, $searchTerm, $userIdString) {
          return $filter->getParentId() === $parentId 
            && $filter->getSearchTerm() === $searchTerm
            && $filter->getUserId() === $userIdString
            && $filter->isRootOnly() === true
            && $filter->shouldIncludeChildren() === false;
        })
      )
      ->willReturn($paginator);

    $handler = new GetTagListHandler($tagRepository);
    $query = new GetTagListQuery(
      parentId: $parentId,
      searchTerm: $searchTerm,
      rootOnly: true,
      includeChildren: false
    );

    $result = $handler($query, $userIdString);

    $this->assertInstanceOf(Collection::class, $result);
  }

  #[Test]
  public function itHandlesOrderingAndSorting(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $tagViews = [];
    
    $userIdString = 'user-sort';
    $orderBy = 'createdAt';
    $order = 'desc';

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator($tagViews));
    $paginator->method('count')->willReturn(0);
    
    $tagRepository->expects($this->once())
      ->method('getAllByPage')
      ->with(
        1,
        $this->callback(function (TagListFilter $filter) use ($orderBy, $order, $userIdString) {
          return $filter->getOrderBy() === $orderBy 
            && $filter->getOrder() === $order
            && $filter->getUserId() === $userIdString;
        })
      )
      ->willReturn($paginator);

    $handler = new GetTagListHandler($tagRepository);
    $query = new GetTagListQuery(orderBy: $orderBy, order: $order);

    $result = $handler($query, $userIdString);

    $this->assertInstanceOf(Collection::class, $result);
  }

  #[Test]
  public function itHandlesEmptyResults(): void {
    $tagRepository = $this->createStub(TagRepositoryInterface::class);
    $tagViews = [];

    $userIdString = 'user-empty';

    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator($tagViews));
    $paginator->method('count')->willReturn(0);

    $tagRepository->method('getAllByPage')->willReturn($paginator);

    $handler = new GetTagListHandler($tagRepository);
    $query = new GetTagListQuery();

    $result = $handler($query, $userIdString);

    $this->assertInstanceOf(Collection::class, $result);
    $this->assertEquals(0, $result->total);
    $this->assertEmpty($result->data);
  }

  #[Test]
  public function itConvertsTagViewsToItems(): void {
    $tagRepository = $this->createStub(TagRepositoryInterface::class);
    $tagView = $this->createStub(TagView::class);
    $tagViews = [$tagView];

    $userIdString = 'user-items';
    $tagIds = ['tag-single'];

    $tagRepository->method('findExactTagsByIds')->willReturn($tagViews);

    $handler = new GetTagListHandler($tagRepository);
    $query = new GetTagListQuery(ids: $tagIds);

    $result = $handler($query, $userIdString);

    $items = $result->data;
    $this->assertCount(1, $items);
    $itemsArray = is_array($items) ? $items : iterator_to_array($items);
    $this->assertInstanceOf(Item::class, $itemsArray[0]);
  }
}