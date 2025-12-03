<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Comment;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Application\Query\GetCommentsByImage\GetCommentsByImageQuery;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryBusInterface;
use UI\Http\Rest\Controller\Comment\GetCommentsController;
use UI\Http\Rest\Response\ApiResponse;

final class GetCommentsControllerTest extends TestCase {
  #[Test]
  public function itReturnsCommentsSuccessfully(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $imageId = 'image-123';
    $collection = new Collection(1, 50, 0, []);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($query) {
        return $query instanceof GetCommentsByImageQuery;
      }))
      ->willReturn($collection);

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $response = $controller($imageId);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itPassesImageIdToQuery(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $imageId = 'specific-image-id';
    $collection = new Collection(0, 50, 0, []);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($query) use ($imageId) {
        if (!$query instanceof GetCommentsByImageQuery) {
          return false;
        }
        return $query->getImageId() === $imageId;
      }))
      ->willReturn($collection);

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $controller($imageId);
  }

  #[Test]
  public function itHandlesPaginationParameters(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $imageId = 'image-123';
    $page = 2;
    $limit = 25;
    $collection = new Collection(100, $limit, ($page - 1) * $limit, []);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($query) use ($page, $limit) {
        if (!$query instanceof GetCommentsByImageQuery) {
          return false;
        }
        return $query->getPage() === $page && $query->getLimit() === $limit;
      }))
      ->willReturn($collection);

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $controller($imageId, $page, $limit);
  }

  #[Test]
  public function itUsesDefaultPaginationValues(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $imageId = 'image-123';
    $collection = new Collection(0, 50, 0, []);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($query) {
        if (!$query instanceof GetCommentsByImageQuery) {
          return false;
        }
        return $query->getPage() === 1 && $query->getLimit() === 50;
      }))
      ->willReturn($collection);

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $controller($imageId);
  }

  #[Test]
  public function itReturnsCollectionResponse(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $imageId = 'image-123';
    $collection = new Collection(5, 50, 0, [
      Item::fromPayload('comment', ['id' => 'comment-1', 'content' => 'First comment']),
      Item::fromPayload('comment', ['id' => 'comment-2', 'content' => 'Second comment']),
    ]);

    $queryBus->method('ask')->willReturn($collection);

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $response = $controller($imageId);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }
}
