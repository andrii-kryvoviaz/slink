<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Collection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryBusInterface;
use Symfony\Component\HttpFoundation\Response;
use UI\Http\Rest\Controller\Collection\GetPublicCollectionController;

final class GetPublicCollectionControllerTest extends TestCase {
  #[Test]
  public function itReturnsCollectionWhenAccessible(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);

    $item = Item::fromPayload('collection', ['id' => 'collection-id']);
    $queryBus->method('ask')->willReturn($item);

    $controller = new GetPublicCollectionController();
    $controller->setQueryBus($queryBus);

    $response = $controller('collection-id');

    $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
  }

  #[Test]
  public function itReturns404WhenCollectionIsNotAccessible(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willReturn(null);

    $controller = new GetPublicCollectionController();
    $controller->setQueryBus($queryBus);

    $response = $controller('collection-id');

    $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
  }
}
