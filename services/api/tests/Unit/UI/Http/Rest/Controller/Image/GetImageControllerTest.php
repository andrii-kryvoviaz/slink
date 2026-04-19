<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Image;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAccessContext;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Infrastructure\Auth\JwtUser;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UI\Http\Rest\Controller\Image\GetImageController;
use UI\Http\Rest\Response\ContentResponse;

final class GetImageControllerTest extends TestCase {
  private function imageView(bool $isPublic, ?string $ownerId = null): ImageView {
    $attributes = $this->createStub(ImageAttributes::class);
    $attributes->method('isPublic')->willReturn($isPublic);

    $owner = null;
    if ($ownerId !== null) {
      $owner = $this->createStub(UserView::class);
      $owner->method('getUuid')->willReturn($ownerId);
    }

    $image = $this->createStub(ImageView::class);
    $image->method('getAttributes')->willReturn($attributes);
    $image->method('getUser')->willReturn($owner);

    return $image;
  }

  private function controller(
    ImageRepositoryInterface $imageRepository,
    AuthorizationCheckerInterface $access,
    QueryBusInterface $queryBus,
    ?CommandBusInterface $commandBus = null,
  ): GetImageController {
    $controller = new GetImageController(
      $imageRepository,
      $access,
    );
    $controller->setQueryBus($queryBus);
    $controller->setCommandBus($commandBus ?? $this->createStub(CommandBusInterface::class));

    return $controller;
  }

  #[Test]
  public function itServesImageWhenAccessIsGranted(): void {
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $imageRepository->method('oneById')->willReturn($this->imageView(isPublic: true));

    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willReturn(Item::fromContent('binary-bytes', 'image/png'));

    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn(true);

    $controller = $this->controller($imageRepository, $access, $queryBus);

    $response = $controller(new GetImageContentQuery(), 'image-id', 'png');

    $this->assertInstanceOf(ContentResponse::class, $response);
    $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
  }

  #[Test]
  public function itThrowsNotFoundWhenAccessIsDenied(): void {
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $imageRepository->method('oneById')->willReturn($this->imageView(isPublic: false, ownerId: 'owner-id'));

    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn(false);

    $controller = $this->controller(
      $imageRepository,
      $access,
      $this->createStub(QueryBusInterface::class),
    );

    $this->expectException(NotFoundException::class);

    $controller(new GetImageContentQuery(), 'image-id', 'png');
  }

  #[Test]
  public function itPassesImageAccessContextToAuthorizationChecker(): void {
    $imageView = $this->imageView(isPublic: false, ownerId: 'owner-id');

    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $imageRepository->method('oneById')->willReturn($imageView);

    $access = $this->createMock(AuthorizationCheckerInterface::class);
    $access->expects($this->once())
      ->method('isGranted')
      ->with(
        ImageAccess::View,
        $this->callback(static function (mixed $context) use ($imageView): bool {
          return $context instanceof ImageAccessContext && $context->image === $imageView;
        }),
      )
      ->willReturn(true);

    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willReturn(Item::fromContent('binary-bytes', 'image/png'));

    $controller = $this->controller($imageRepository, $access, $queryBus);

    $owner = $this->createStub(JwtUser::class);
    $owner->method('getIdentifier')->willReturn('owner-id');

    $response = $controller(new GetImageContentQuery(), 'image-id', 'png', $owner);

    $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
  }

  #[Test]
  public function itPropagatesImageLookupNotFound(): void {
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $imageRepository->method('oneById')->willThrowException(new NotFoundException());

    $controller = $this->controller(
      $imageRepository,
      $this->createStub(AuthorizationCheckerInterface::class),
      $this->createStub(QueryBusInterface::class),
    );

    $this->expectException(NotFoundException::class);

    $controller(new GetImageContentQuery(), 'image-id', 'png');
  }
}
