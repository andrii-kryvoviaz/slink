<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Application\Query\GetCollectionItemsExists;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Application\Query\GetCollectionItemsExists\GetCollectionItemsExistsHandler;
use Slink\Collection\Application\Query\GetCollectionItemsExists\GetCollectionItemsExistsQuery;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;

final class GetCollectionItemsExistsHandlerTest extends TestCase {
  private CollectionItemRepositoryInterface&Stub $collectionItemRepository;

  protected function setUp(): void {
    parent::setUp();

    $this->collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);
  }

  private function createHandler(): GetCollectionItemsExistsHandler {
    return new GetCollectionItemsExistsHandler($this->collectionItemRepository);
  }

  #[Test]
  public function itReturnsTrueWhenCollectionHasItems(): void {
    $this->collectionItemRepository->method('existsByCollectionId')->willReturn(true);

    $handler = $this->createHandler();
    $result = $handler(new GetCollectionItemsExistsQuery('collection-id'));

    $this->assertTrue($result);
  }

  #[Test]
  public function itReturnsFalseWhenCollectionIsEmpty(): void {
    $this->collectionItemRepository->method('existsByCollectionId')->willReturn(false);

    $handler = $this->createHandler();
    $result = $handler(new GetCollectionItemsExistsQuery('collection-id'));

    $this->assertFalse($result);
  }
}
