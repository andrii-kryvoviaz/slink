<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Image;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Infrastructure\Auth\JwtUser;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UI\Http\Rest\Controller\Image\GetPublicImageByIdController;

final class GetPublicImageByIdControllerTest extends TestCase {
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

  #[Test]
  public function itReturnsImageWhenAccessIsGranted(): void {
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $imageRepository->method('oneById')->willReturn($this->imageView(isPublic: true));

    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willReturn(Item::fromPayload('image', ['id' => 'image-id']));

    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn(true);

    $controller = new GetPublicImageByIdController($imageRepository, $access);
    $controller->setQueryBus($queryBus);

    $response = $controller('image-id');

    $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
  }

  #[Test]
  public function itThrowsNotFoundWhenAccessIsDenied(): void {
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $imageRepository->method('oneById')->willReturn($this->imageView(isPublic: false, ownerId: 'owner-id'));

    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn(false);

    $controller = new GetPublicImageByIdController($imageRepository, $access);
    $controller->setQueryBus($this->createStub(QueryBusInterface::class));

    $this->expectException(NotFoundException::class);

    $controller('image-id');
  }

  #[Test]
  public function itReturnsImageForOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $imageRepository->method('oneById')->willReturn($this->imageView(isPublic: false, ownerId: $ownerId));

    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willReturn(Item::fromPayload('image', ['id' => 'image-id']));

    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn(true);

    $owner = $this->createStub(JwtUser::class);
    $owner->method('getIdentifier')->willReturn($ownerId);

    $controller = new GetPublicImageByIdController($imageRepository, $access);
    $controller->setQueryBus($queryBus);

    $response = $controller('image-id', $owner);

    $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
  }
}
