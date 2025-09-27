<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Tag;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\Tag\Application\Query\GetImageTags\GetImageTagsQuery;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use Slink\User\Domain\Contracts\UserInterface;
use UI\Http\Rest\Controller\Tag\GetImageTagsController;
use UI\Http\Rest\Response\ApiResponse;

final class GetImageTagsControllerTest extends TestCase {

  #[Test]
  public function itReturnsImageTags(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    $collection = new Collection(1, 2, 2, []);
    
    $user->method('getIdentifier')->willReturn('user-123');

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->isInstanceOf(GetImageTagsQuery::class))
      ->willReturn($collection);

    $controller = new GetImageTagsController();
    $controller->setQueryBus($queryBus);

    $response = $controller('image-123', $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itCreatesQueryWithCorrectImageId(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    $collection = new Collection(1, 0, 0, []);
    
    $user->method('getIdentifier')->willReturn('user-456');

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function (GetImageTagsQuery $query) {
        return $query->getImageId() === 'image-456';
      }))
      ->willReturn($collection);

    $controller = new GetImageTagsController();
    $controller->setQueryBus($queryBus);

    $response = $controller('image-456', $user);

    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itReturnsCollectionResponse(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    $collection = new Collection(1, 3, 3, []);
    
    $user->method('getIdentifier')->willReturn('user-789');
    $queryBus->method('ask')->willReturn($collection);

    $controller = new GetImageTagsController();
    $controller->setQueryBus($queryBus);

    $response = $controller('image-789', $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itHandlesEmptyTagList(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    $collection = new Collection(1, 0, 0, []);
    
    $user->method('getIdentifier')->willReturn('user-empty');
    $queryBus->method('ask')->willReturn($collection);

    $controller = new GetImageTagsController();
    $controller->setQueryBus($queryBus);

    $response = $controller('image-empty', $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }
}