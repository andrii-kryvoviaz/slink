<?php

declare(strict_types=1);

namespace Tests\Integration\Slink\Tag\Application;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Application\Command\CreateTag\CreateTagCommand;
use Slink\Tag\Application\Command\CreateTag\CreateTagHandler;
use Slink\Tag\Application\Command\DeleteTag\DeleteTagCommand;
use Slink\Tag\Application\Command\DeleteTag\DeleteTagHandler;
use Slink\Tag\Application\Query\GetTagById\GetTagByIdHandler;
use Slink\Tag\Application\Query\GetTagById\GetTagByIdQuery;
use Slink\Tag\Application\Query\GetTagList\GetTagListHandler;
use Slink\Tag\Application\Query\GetTagList\GetTagListQuery;
use Slink\Tag\Domain\Exception\DuplicateTagException;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Specification\TagDuplicateSpecificationInterface;
use Slink\Tag\Domain\Tag;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;

final class TagWorkflowIntegrationTest extends TestCase {

  #[Test]
  public function itCompletesFullTagWorkflow(): void {
    $tagStore = $this->createStub(TagStoreRepositoryInterface::class);
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);

    $userId = 'user-123';
    $tagView = $this->createStub(TagView::class);
    
    $duplicateSpec->method('ensureUnique');
    $tagStore->method('store');
    $tagStore->method('get')->willReturn($this->createMock(Tag::class));
    $tagRepository->method('findById')->willReturn($tagView);
    $tagRepository->method('getAllByPage')->willReturn(new \ArrayIterator([$tagView]));
    $tagRepository->method('getTotalCount')->willReturn(1);

    $createHandler = new CreateTagHandler($tagStore, $duplicateSpec);
    $getByIdHandler = new GetTagByIdHandler($tagRepository);
    $getListHandler = new GetTagListHandler($tagRepository);
    $deleteHandler = new DeleteTagHandler($tagStore, $tagRepository);

    $createCommand = new CreateTagCommand('integration-test-tag');
    $createdTagId = $createHandler($createCommand, $userId);
    $this->assertInstanceOf(ID::class, $createdTagId);

    $getByIdQuery = new GetTagByIdQuery($createdTagId->toString());
    $retrievedTag = $getByIdHandler($getByIdQuery, $userId);
    $this->assertInstanceOf(TagView::class, $retrievedTag);

    $getListQuery = new GetTagListQuery();
    $tagList = $getListHandler($getListQuery, $userId);
    $this->assertEquals(1, $tagList->total);

    $deleteCommand = new DeleteTagCommand($createdTagId->toString());
    $deleteHandler($deleteCommand, $userId);
    
    $this->expectNotToPerformAssertions();
  }

  #[Test]
  public function itHandlesDuplicateTagCreation(): void {
    $tagStore = $this->createStub(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);

    $duplicateSpec->method('ensureUnique')
      ->willThrowException(new DuplicateTagException('duplicate-tag'));

    $createHandler = new CreateTagHandler($tagStore, $duplicateSpec);
    $createCommand = new CreateTagCommand('duplicate-tag');

    $this->expectException(DuplicateTagException::class);
    $createHandler($createCommand, 'user-456');
  }

  #[Test]
  public function itCreatesNestedTagHierarchy(): void {
    $tagStore = $this->createStub(TagStoreRepositoryInterface::class);
    $duplicateSpec = $this->createStub(TagDuplicateSpecificationInterface::class);

    $parentTag = $this->createStub(Tag::class);
    $parentTag->method('getPath')->willReturn(\Slink\Tag\Domain\ValueObject\TagPath::fromString('#parent'));

    $duplicateSpec->method('ensureUnique');
    $tagStore->method('store');
    $tagStore->method('get')->willReturn($parentTag);

    $createHandler = new CreateTagHandler($tagStore, $duplicateSpec);

    // Create parent tag
    $parentCommand = new CreateTagCommand('parent-tag');
    $parentId = $createHandler($parentCommand, 'user-789');

    // Create child tag
    $childCommand = new CreateTagCommand('child-tag', $parentId->toString());
    $childId = $createHandler($childCommand, 'user-789');

    $this->assertInstanceOf(ID::class, $parentId);
    $this->assertInstanceOf(ID::class, $childId);
    $this->assertNotEquals($parentId->toString(), $childId->toString());
  }

  #[Test]
  public function itFiltersTagsByParent(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $tagView = $this->createStub(TagView::class);

    $parentId = 'parent-123';
    $userId = 'user-filter';

    $tagRepository->expects($this->once())
      ->method('getAllByPage')
      ->with(
        1,
        $this->callback(function ($filter) use ($parentId, $userId) {
          return $filter->getParentId() === $parentId 
            && $filter->getUserId() === $userId;
        })
      )
      ->willReturn(new \ArrayIterator([$tagView]));

    $tagRepository->method('getTotalCount')->willReturn(1);

    $getListHandler = new GetTagListHandler($tagRepository);
    $query = new GetTagListQuery(parentId: $parentId);

    $result = $getListHandler($query, $userId);
    $this->assertEquals(1, $result->total);
  }

  #[Test]
  public function itSearchesTagsByName(): void {
    $tagRepository = $this->createMock(TagRepositoryInterface::class);
    $tagView = $this->createStub(TagView::class);

    $searchTerm = 'search-term';
    $userId = 'user-search';

    $tagRepository->expects($this->once())
      ->method('getAllByPage')
      ->with(
        1,
        $this->callback(function ($filter) use ($searchTerm, $userId) {
          return $filter->getSearchTerm() === $searchTerm 
            && $filter->getUserId() === $userId;
        })
      )
      ->willReturn(new \ArrayIterator([$tagView]));

    $tagRepository->method('getTotalCount')->willReturn(1);

    $getListHandler = new GetTagListHandler($tagRepository);
    $query = new GetTagListQuery(searchTerm: $searchTerm);

    $result = $getListHandler($query, $userId);
    $this->assertEquals(1, $result->total);
  }
}