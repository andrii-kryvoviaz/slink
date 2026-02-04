<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Tag;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\Tag\Application\Query\GetTagList\GetTagListQuery;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use Slink\User\Domain\Contracts\UserInterface;
use UI\Http\Rest\Controller\Tag\GetTagListController;
use UI\Http\Rest\Response\ApiResponse;

final class GetTagListControllerTest extends TestCase {

  #[Test]
  public function itReturnsTagList(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    $collection = new Collection(1, 50, 2, []);
    
    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-123');

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->isInstanceOf(\Symfony\Component\Messenger\Envelope::class))
      ->willReturn($collection);

    $controller = new GetTagListController();
    $controller->setQueryBus($queryBus);

    $query = new GetTagListQuery();

    $response = $controller($query, $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itHandlesQueryWithFilters(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    $collection = new Collection(1, 25, 1, []);
    
    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-456');

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($envelope) {
        return $envelope instanceof \Symfony\Component\Messenger\Envelope 
          && $envelope->getMessage() instanceof GetTagListQuery;
      }))
      ->willReturn($collection);

    $controller = new GetTagListController();
    $controller->setQueryBus($queryBus);

    $query = new GetTagListQuery(
      limit: 25,
      orderBy: 'name',
      order: 'desc',
      searchTerm: 'test'
    );

    $response = $controller($query, $user);

    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itHandlesParentIdFilter(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createStub(UserInterface::class);
    $collection = new Collection(1, 50, 0, []);

    $user->method('getIdentifier')->willReturn('user-789');

    $queryBus->expects($this->once())
      ->method('ask')
      ->willReturn($collection);

    $controller = new GetTagListController();
    $controller->setQueryBus($queryBus);

    $query = new GetTagListQuery(parentId: 'parent-tag-123');

    $response = $controller($query, $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itHandlesRootOnlyFilter(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $user = $this->createStub(UserInterface::class);
    $collection = new Collection(1, 50, 1, []);

    $user->method('getIdentifier')->willReturn('user-abc');
    $queryBus->method('ask')->willReturn($collection);

    $controller = new GetTagListController();
    $controller->setQueryBus($queryBus);

    $query = new GetTagListQuery(rootOnly: true);

    $response = $controller($query, $user);

    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itReturnsCollectionResponse(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $user = $this->createStub(UserInterface::class);
    $collection = new Collection(1, 50, 0, []);

    $user->method('getIdentifier')->willReturn('user-def');
    $queryBus->method('ask')->willReturn($collection);

    $controller = new GetTagListController();
    $controller->setQueryBus($queryBus);

    $query = new GetTagListQuery();

    $response = $controller($query, $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }
}