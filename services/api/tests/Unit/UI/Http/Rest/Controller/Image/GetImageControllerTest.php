<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Image;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryBusInterface;
use Symfony\Component\HttpFoundation\Response;
use UI\Http\Rest\Controller\Image\GetImageController;
use UI\Http\Rest\Response\ContentResponse;

final class GetImageControllerTest extends TestCase {
  private function controller(
    QueryBusInterface $queryBus,
    ?CommandBusInterface $commandBus = null,
  ): GetImageController {
    $controller = new GetImageController();
    $controller->setQueryBus($queryBus);
    $controller->setCommandBus($commandBus ?? $this->createStub(CommandBusInterface::class));

    return $controller;
  }

  #[Test]
  public function itServesImageFromQueryBus(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willReturn(Item::fromContent('binary-bytes', 'image/png'));

    $controller = $this->controller($queryBus);

    $response = $controller(new GetImageContentQuery(), 'image-id', 'png');

    $this->assertInstanceOf(ContentResponse::class, $response);
    $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
  }
}
