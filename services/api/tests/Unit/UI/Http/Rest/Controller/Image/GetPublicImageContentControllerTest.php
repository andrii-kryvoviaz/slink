<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Image;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetPublicImageContent\GetPublicImageContentQuery;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryBusInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use UI\Http\Rest\Controller\Image\GetPublicImageContentController;
use UI\Http\Rest\Response\ContentResponse;

final class GetPublicImageContentControllerTest extends TestCase {
  private function controller(
    QueryBusInterface $queryBus,
    ?CommandBusInterface $commandBus = null,
  ): GetPublicImageContentController {
    $controller = new GetPublicImageContentController();
    $controller->setQueryBus($queryBus);
    $controller->setCommandBus($commandBus ?? $this->createStub(CommandBusInterface::class));

    return $controller;
  }

  #[Test]
  public function itServesImageFromQueryBus(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willReturn(Item::fromContent('binary-bytes', 'image/png'));

    $controller = $this->controller($queryBus);

    $response = $controller('image-id');

    $this->assertInstanceOf(ContentResponse::class, $response);
    $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
  }

  #[Test]
  public function itDispatchesPublicQueryWithImageId(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $queryBus
      ->expects($this->once())
      ->method('ask')
      ->with($this->callback(static function (mixed $query): bool {
        return $query instanceof GetPublicImageContentQuery
          && $query->imageId === 'image-id';
      }))
      ->willReturn(Item::fromContent('binary-bytes', 'image/png'));

    $controller = $this->controller($queryBus);

    $response = $controller('image-id');

    $this->assertInstanceOf(ContentResponse::class, $response);
  }
}
