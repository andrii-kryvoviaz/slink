<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Application\Query\GetPublicImageContent;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetPublicImageContent\GetPublicImageContentHandler;
use Slink\Image\Application\Query\GetPublicImageContent\GetPublicImageContentQuery;
use Slink\Image\Application\Service\ImageContentLoader;
use Slink\Image\Domain\ValueObject\ImageContent;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Http\CachePolicy;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class GetPublicImageContentHandlerTest extends TestCase {
  private ImageRepositoryInterface&Stub $repository;
  private ImageContentLoader&Stub $loader;
  private ConfigurationProviderInterface&Stub $configurationProvider;

  public function setUp(): void {
    parent::setUp();

    $this->repository = $this->createStub(ImageRepositoryInterface::class);
    $this->loader = $this->createStub(ImageContentLoader::class);
    $this->loader->method('load')->willReturn(new ImageContent('binary-bytes', 'image/png'));
    $this->configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
    $this->configurationProvider->method('get')->willReturn(80);
  }

  private function createHandler(): GetPublicImageContentHandler {
    return new GetPublicImageContentHandler(
      $this->repository,
      $this->loader,
      $this->configurationProvider,
      1920,
    );
  }

  private function image(string $id, bool $isPublic): ImageView&Stub {
    $image = $this->createStub(ImageView::class);
    $image->method('getAttributes')->willReturn(ImageAttributes::create($id, 'description', $isPublic));
    $image->method('getMimeType')->willReturn('image/png');
    $image->method('getFileName')->willReturn("{$id}.png");

    return $image;
  }

  #[Test]
  public function itServesPublicImage(): void {
    $imageId = 'public-image';
    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $this->image($imageId, true)],
    ]);

    $result = $this->createHandler()(new GetPublicImageContentQuery($imageId));

    $this->assertInstanceOf(Item::class, $result);
    $this->assertSame('binary-bytes', $result->resource);
    $this->assertSame('image/png', $result->type);
  }

  #[Test]
  public function itThrowsNotFoundForNonPublicImage(): void {
    $imageId = 'private-image';
    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $this->image($imageId, false)],
    ]);

    $this->expectException(NotFoundException::class);
    $this->createHandler()(new GetPublicImageContentQuery($imageId));
  }

  #[Test]
  public function itAlwaysReturnsRevocableCache(): void {
    $imageId = 'public-image';
    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $this->image($imageId, true)],
    ]);

    $result = $this->createHandler()(new GetPublicImageContentQuery($imageId));

    $this->assertEquals(CachePolicy::revocable(), $result->cachePolicy);
  }

  #[Test]
  public function itDelegatesContentRetrievalToLoaderWithMaxWidthAndConfiguredQuality(): void {
    $imageId = 'public-image';
    $image = $this->image($imageId, true);

    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $image],
    ]);

    $loader = $this->createMock(ImageContentLoader::class);
    $loader
      ->expects($this->once())
      ->method('load')
      ->with($image, null, ['width' => 1920, 'quality' => 80])
      ->willReturn(new ImageContent('delegated-bytes', 'image/png'));

    $handler = new GetPublicImageContentHandler(
      $this->repository,
      $loader,
      $this->configurationProvider,
      1920,
    );

    $result = $handler(new GetPublicImageContentQuery($imageId));

    $this->assertSame('delegated-bytes', $result->resource);
  }

  #[Test]
  public function itDoesNotInvokeAuthorizationChecker(): void {
    $access = $this->createMock(AuthorizationCheckerInterface::class);
    $access->expects($this->never())->method('isGranted');

    $imageId = 'public-image';
    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $this->image($imageId, true)],
    ]);

    $this->createHandler()(new GetPublicImageContentQuery($imageId));
  }
}
