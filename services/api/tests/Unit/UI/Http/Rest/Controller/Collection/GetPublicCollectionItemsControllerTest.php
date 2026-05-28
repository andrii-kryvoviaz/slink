<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Collection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Application\Query\GetCollectionItems\GetCollectionItemsQuery;
use Slink\Shared\Application\Query\QueryBusInterface;
use Symfony\Component\HttpFoundation\Response;
use UI\Http\Rest\Controller\Collection\GetPublicCollectionItemsController;

final class GetPublicCollectionItemsControllerTest extends TestCase {
  #[Test]
  public function itReturns404WhenCollectionIsNotAccessible(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willReturn(null);

    $controller = new GetPublicCollectionItemsController();
    $controller->setQueryBus($queryBus);

    $itemsQuery = new GetCollectionItemsQuery();
    $response = $controller('collection-id', $itemsQuery);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
  }
}
