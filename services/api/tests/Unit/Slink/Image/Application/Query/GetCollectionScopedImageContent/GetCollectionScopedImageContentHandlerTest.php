<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Application\Query\GetCollectionScopedImageContent;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Enum\CollectionScopedImageAccess;
use Slink\Collection\Domain\ValueObject\CollectionScopedImageAccessContext;
use Slink\Image\Application\Query\GetCollectionScopedImageContent\GetCollectionScopedImageContentHandler;
use Slink\Image\Application\Query\GetCollectionScopedImageContent\GetCollectionScopedImageContentQuery;
use Slink\Image\Application\Service\ImageContentLoader;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageContent;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\CachePolicy;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class GetCollectionScopedImageContentHandlerTest extends TestCase {
  private ImageRepositoryInterface&Stub $repository;
  private AuthorizationCheckerInterface&Stub $access;
  private ImageContentLoader&Stub $loader;

  protected function setUp(): void {
    parent::setUp();

    $this->repository = $this->createStub(ImageRepositoryInterface::class);
    $this->access = $this->createStub(AuthorizationCheckerInterface::class);
    $this->loader = $this->createStub(ImageContentLoader::class);

    $this->loader->method('load')->willReturn(new ImageContent('bytes', 'image/jpeg'));
  }

  private function createHandler(int $maxWidth = 1920): GetCollectionScopedImageContentHandler {
    return new GetCollectionScopedImageContentHandler(
      $this->repository,
      $this->access,
      $this->loader,
      $maxWidth,
    );
  }

  private function createImageView(string $itemId): ImageView&Stub {
    $image = $this->createStub(ImageView::class);
    $image->method('getUuid')->willReturn($itemId);
    $image->method('getFileName')->willReturn("{$itemId}.jpg");
    $image->method('getMimeType')->willReturn('image/jpeg');
    $image->method('getAttributes')->willReturn(ImageAttributes::create($itemId, '', false));

    return $image;
  }

  #[Test]
  public function itThrowsNotFoundWhenVoterDenies(): void {
    $this->access->method('isGranted')->willReturn(false);
    $this->repository->method('oneById')->willReturn($this->createImageView('item-id'));

    $loader = $this->createMock(ImageContentLoader::class);
    $loader->expects($this->never())->method('load');

    $handler = new GetCollectionScopedImageContentHandler(
      $this->repository,
      $this->access,
      $loader,
      1920,
    );

    $this->expectException(NotFoundException::class);

    $handler(new GetCollectionScopedImageContentQuery('collection-id', 'item-id'));
  }

  #[Test]
  public function itReturnsImageWithRevocableCacheWhenVoterGrants(): void {
    $itemId = 'item-id';
    $this->access->method('isGranted')->willReturn(true);
    $this->repository->method('oneById')->willReturn($this->createImageView($itemId));

    $result = $this->createHandler()(
      new GetCollectionScopedImageContentQuery('collection-id', $itemId),
    );

    $this->assertInstanceOf(Item::class, $result);
    $this->assertEquals(CachePolicy::revocable(), $result->cachePolicy);
  }

  #[Test]
  public function itChecksAccessWithCollectionScopedImageAccessContext(): void {
    $itemId = 'item-id';
    $collectionId = 'collection-id';
    $imageView = $this->createImageView($itemId);

    $access = $this->createMock(AuthorizationCheckerInterface::class);
    $access
      ->expects($this->once())
      ->method('isGranted')
      ->with(
        CollectionScopedImageAccess::View,
        $this->callback(static fn (mixed $subject): bool => $subject instanceof CollectionScopedImageAccessContext
          && $subject->collectionId === $collectionId
          && $subject->itemId === $itemId
          && $subject->imageView === $imageView),
      )
      ->willReturn(true);

    $this->repository->method('oneById')->willReturn($imageView);

    $handler = new GetCollectionScopedImageContentHandler(
      $this->repository,
      $access,
      $this->loader,
      1920,
    );

    $handler(new GetCollectionScopedImageContentQuery($collectionId, $itemId));
  }

  #[Test]
  public function itCallsLoaderWithConfiguredMaxWidth(): void {
    $itemId = 'item-id';
    $image = $this->createImageView($itemId);

    $this->access->method('isGranted')->willReturn(true);
    $this->repository->method('oneById')->willReturn($image);

    $loader = $this->createMock(ImageContentLoader::class);
    $loader
      ->expects($this->once())
      ->method('load')
      ->with($image, null, ['width' => 1920])
      ->willReturn(new ImageContent('bytes', 'image/jpeg'));

    $handler = new GetCollectionScopedImageContentHandler(
      $this->repository,
      $this->access,
      $loader,
      1920,
    );

    $handler(new GetCollectionScopedImageContentQuery('collection-id', $itemId));
  }
}
