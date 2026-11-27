<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Image;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetPublicImageById\GetPublicImageByIdQuery;
use Slink\Image\Infrastructure\Resource\ImageResourceContext;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;
use UI\Http\Rest\Controller\Image\GetPublicImageByIdController;

final class GetPublicImageByIdControllerTest extends TestCase {
  private function controller(QueryBusInterface $queryBus): GetPublicImageByIdController {
    $controller = new GetPublicImageByIdController();
    $controller->setQueryBus($queryBus);

    return $controller;
  }

  #[Test]
  public function itDispatchesPublicQueryWithImageId(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $queryBus
      ->expects($this->once())
      ->method('ask')
      ->with($this->callback(static function (mixed $envelope): bool {
        if (!$envelope instanceof Envelope) {
          return false;
        }

        $query = $envelope->getMessage();
        if (!$query instanceof GetPublicImageByIdQuery) {
          return false;
        }

        return $query->getId() === 'image-id';
      }))
      ->willReturn(Item::fromPayload('image', ['id' => 'image-id']));

    $controller = $this->controller($queryBus);

    $controller('image-id');
  }

  #[Test]
  public function itPassesViewerUserIdToResourceContext(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    $queryBus = $this->createMock(QueryBusInterface::class);
    $queryBus
      ->expects($this->once())
      ->method('ask')
      ->with($this->callback(static function (mixed $envelope) use ($ownerId): bool {
        if (!$envelope instanceof Envelope) {
          return false;
        }

        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }

        $context = $stamp->getAdditionalArguments();
        $resourceContext = $context['resourceContext'] ?? null;

        return $resourceContext instanceof ImageResourceContext
          && $resourceContext->viewerUserId === $ownerId;
      }))
      ->willReturn(Item::fromPayload('image', ['id' => 'image-id']));

    $owner = $this->createStub(JwtUser::class);
    $owner->method('getIdentifier')->willReturn($ownerId);

    $controller = $this->controller($queryBus);

    $controller('image-id', $owner);
  }
}
