<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Collection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryBusInterface;
use Symfony\Component\HttpFoundation\Response;
use UI\Http\Rest\Controller\Collection\GetCollectionItemsExistsController;

final class GetCollectionItemsExistsControllerTest extends TestCase {
  #[Test]
  public function itReturns404WhenCollectionIsNotAccessible(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willReturn(null);

    $controller = new GetCollectionItemsExistsController();
    $controller->setQueryBus($queryBus);

    $response = $controller('collection-id');

    $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
  }

  #[Test]
  public function itReturnsExistsTrueWhenCollectionHasItems(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willReturnOnConsecutiveCalls(
      $this->createStub(Item::class),
      true,
    );

    $controller = new GetCollectionItemsExistsController();
    $controller->setQueryBus($queryBus);

    $response = $controller('collection-id');

    $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    $this->assertSame(['exists' => true], json_decode((string) $response->getContent(), true));
  }

  #[Test]
  public function itReturnsExistsFalseWhenCollectionIsEmpty(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willReturnOnConsecutiveCalls(
      $this->createStub(Item::class),
      false,
    );

    $controller = new GetCollectionItemsExistsController();
    $controller->setQueryBus($queryBus);

    $response = $controller('collection-id');

    $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    $this->assertSame(['exists' => false], json_decode((string) $response->getContent(), true));
  }
}
