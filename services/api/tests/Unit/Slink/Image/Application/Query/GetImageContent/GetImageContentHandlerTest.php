<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Application\Query\GetImageContent;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetImageContent\GetImageContentHandler;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slink\Image\Application\Service\ImageContentLoader;
use Slink\Image\Domain\ValueObject\ImageContent;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Shared\Application\Http\CachePolicy;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Domain\Service\UrlSignatureInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class GetImageContentHandlerTest extends TestCase {
  private ImageRepositoryInterface&Stub $repository;
  private ShareUrlBuilderInterface&Stub $shareUrlBuilder;
  private AuthorizationCheckerInterface&Stub $access;
  private ImageContentLoader&Stub $loader;
  private UrlSignatureInterface&Stub $signature;

  public function setUp(): void {
    parent::setUp();

    $this->repository = $this->createStub(ImageRepositoryInterface::class);
    $this->shareUrlBuilder = $this->createStub(ShareUrlBuilderInterface::class);
    $this->access = $this->createStub(AuthorizationCheckerInterface::class);
    $this->loader = $this->createStub(ImageContentLoader::class);
    $this->signature = $this->createStub(UrlSignatureInterface::class);

    $this->shareUrlBuilder->method('buildTargetPath')->willReturn(TargetPath::fromString('/image/test.jpg'));
    $this->access->method('isGranted')->willReturn(true);
    $this->loader->method('load')->willReturn(new ImageContent('bytes', 'image/jpeg'));
    $this->signature->method('verify')->willReturn(true);
  }

  private function createHandler(): GetImageContentHandler {
    return new GetImageContentHandler(
      $this->repository,
      $this->access,
      $this->shareUrlBuilder,
      $this->loader,
      $this->signature,
    );
  }

  #[Test]
  public function itDelegatesContentRetrievalToLoader(): void {
    $imageId = 'test-file-name';
    $fileName = $imageId;

    $image = $this->createStub(ImageView::class);
    $image->method('getAttributes')->willReturn(ImageAttributes::create($imageId, 'description', true));
    $image->method('getMimeType')->willReturn('image/jpeg');
    $image->method('getFileName')->willReturn((string) $fileName);

    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $image],
    ]);

    $loader = $this->createMock(ImageContentLoader::class);
    $loader
      ->expects($this->once())
      ->method('load')
      ->with(
        $image,
        null,
        $this->callback(static fn (array $params): bool => array_key_exists('width', $params)),
      )
      ->willReturn(new ImageContent('binary-bytes', 'image/jpeg'));

    $handler = new GetImageContentHandler(
      $this->repository,
      $this->access,
      $this->shareUrlBuilder,
      $loader,
      $this->signature,
    );

    $result = $handler(new GetImageContentQuery()->withImageId($fileName));

    $this->assertInstanceOf(Item::class, $result);
    $this->assertEquals('binary-bytes', $result->resource);
    $this->assertEquals('image/jpeg', $result->type);
  }

  #[Test]
  public function itPropagatesLoaderNotFoundException(): void {
    $imageId = 'test-file-name';
    $fileName = $imageId;

    $this->repository->method('oneById')->willReturnCallback(function (string $id) use ($imageId): never {
      if ($id === $imageId) {
        throw new NotFoundException();
      }
      throw new \LogicException('Unexpected id: ' . $id);
    });

    $this->expectException(NotFoundException::class);
    $this->createHandler()(new GetImageContentQuery()->withImageId($fileName));
  }

  #[Test]
  public function itServesImageWhenAccessGranted(): void {
    $fileName = 'test-file-name';

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn((string) $fileName);
    $image->method('getMimeType')->willReturn('image/jpeg');
    $image->method('getAttributes')->willReturn(ImageAttributes::create((string) $fileName, '', true));
    $image->method('getUser')->willReturn(null);

    $this->repository->method('oneById')->willReturn($image);

    $result = $this->createHandler()(new GetImageContentQuery()->withImageId($fileName));

    $this->assertInstanceOf(Item::class, $result);
  }

  #[Test]
  public function itThrowsNotFoundWhenAccessDenied(): void {
    $fileName = 'test-file-name';

    $this->access = $this->createStub(AuthorizationCheckerInterface::class);
    $this->access->method('isGranted')->willReturn(false);

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn((string) $fileName);
    $image->method('getMimeType')->willReturn('image/jpeg');

    $this->repository->method('oneById')->willReturn($image);

    $this->expectException(NotFoundException::class);
    $this->createHandler()(new GetImageContentQuery()->withImageId($fileName));
  }

  #[Test]
  public function itForwardsRequestedFormatToLoader(): void {
    $imageId = 'test-file-name';
    $fileName = $imageId;

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn('test-file-name.png');
    $image->method('getMimeType')->willReturn('image/png');
    $image->method('getAttributes')->willReturn(ImageAttributes::create($imageId, '', true));

    $this->repository->method('oneById')->willReturn($image);

    $loader = $this->createMock(ImageContentLoader::class);
    $loader
      ->expects($this->once())
      ->method('load')
      ->with($image, 'webp', $this->callback(static fn (mixed $v): bool => is_array($v)))
      ->willReturn(new ImageContent('converted', 'image/webp'));

    $handler = new GetImageContentHandler(
      $this->repository,
      $this->access,
      $this->shareUrlBuilder,
      $loader,
      $this->signature,
    );

    $result = $handler(new GetImageContentQuery()->withImageId($fileName)->withFormat('webp'));

    $this->assertEquals('image/webp', $result->type);
    $this->assertEquals('converted', $result->resource);
  }

  #[Test]
  public function itAppliesTransformsWhenSignatureValid(): void {
    $fileName = 'transform-test';

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn($fileName);
    $image->method('getMimeType')->willReturn('image/jpeg');
    $image->method('getAttributes')->willReturn(ImageAttributes::create($fileName, '', true));

    $this->repository->method('oneById')->willReturn($image);

    $signature = $this->createStub(UrlSignatureInterface::class);
    $signature->method('verify')->willReturn(true);

    $loader = $this->createMock(ImageContentLoader::class);
    $loader
      ->expects($this->once())
      ->method('load')
      ->with(
        $image,
        null,
        $this->callback(static fn (array $params): bool => ($params['width'] ?? null) === 100),
      )
      ->willReturn(new ImageContent('bytes', 'image/jpeg'));

    $handler = new GetImageContentHandler(
      $this->repository,
      $this->access,
      $this->shareUrlBuilder,
      $loader,
      $signature,
    );

    $handler(new GetImageContentQuery(width: 100, s: 'sig')->withImageId($fileName));
  }

  #[Test]
  public function itDropsTransformsWhenSignatureMissing(): void {
    $fileName = 'transform-test';

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn($fileName);
    $image->method('getMimeType')->willReturn('image/jpeg');
    $image->method('getAttributes')->willReturn(ImageAttributes::create($fileName, '', true));

    $this->repository->method('oneById')->willReturn($image);

    $loader = $this->createMock(ImageContentLoader::class);
    $loader
      ->expects($this->once())
      ->method('load')
      ->with($image, null, [])
      ->willReturn(new ImageContent('bytes', 'image/jpeg'));

    $handler = new GetImageContentHandler(
      $this->repository,
      $this->access,
      $this->shareUrlBuilder,
      $loader,
      $this->signature,
    );

    $handler(new GetImageContentQuery(width: 100)->withImageId($fileName));
  }

  #[Test]
  public function itDropsTransformsWhenSignatureInvalid(): void {
    $fileName = 'transform-test';

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn($fileName);
    $image->method('getMimeType')->willReturn('image/jpeg');
    $image->method('getAttributes')->willReturn(ImageAttributes::create($fileName, '', true));

    $this->repository->method('oneById')->willReturn($image);

    $signature = $this->createStub(UrlSignatureInterface::class);
    $signature->method('verify')->willReturn(false);

    $loader = $this->createMock(ImageContentLoader::class);
    $loader
      ->expects($this->once())
      ->method('load')
      ->with($image, null, [])
      ->willReturn(new ImageContent('bytes', 'image/jpeg'));

    $handler = new GetImageContentHandler(
      $this->repository,
      $this->access,
      $this->shareUrlBuilder,
      $loader,
      $signature,
    );

    $handler(new GetImageContentQuery(width: 100, s: 'bad-sig')->withImageId($fileName));
  }

  #[Test]
  public function itPassesQualityOnlyTransformsWithoutSignature(): void {
    $fileName = 'transform-test';

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn($fileName);
    $image->method('getMimeType')->willReturn('image/jpeg');
    $image->method('getAttributes')->willReturn(ImageAttributes::create($fileName, '', true));

    $this->repository->method('oneById')->willReturn($image);

    $loader = $this->createMock(ImageContentLoader::class);
    $loader
      ->expects($this->once())
      ->method('load')
      ->with(
        $image,
        null,
        $this->callback(static fn (array $params): bool => ($params['quality'] ?? null) === 80),
      )
      ->willReturn(new ImageContent('bytes', 'image/jpeg'));

    $handler = new GetImageContentHandler(
      $this->repository,
      $this->access,
      $this->shareUrlBuilder,
      $loader,
      $this->signature,
    );

    $handler(new GetImageContentQuery(quality: 80)->withImageId($fileName));
  }

  #[Test]
  public function itSelectsRevocableCacheForPublicImageViewedByGuest(): void {
    $result = $this->serveForCache(isPublic: true);

    $this->assertEquals(CachePolicy::revocable(), $result->cachePolicy);
  }

  #[Test]
  public function itSelectsRevocableCacheForPrivateImage(): void {
    $result = $this->serveForCache(isPublic: false);

    $this->assertEquals(CachePolicy::revocable(), $result->cachePolicy);
  }

  #[Test]
  public function itSelectsPrivateImmutableCacheForOwnerViewingOwnPublicImage(): void {
    $ownerId = '11111111-1111-1111-1111-111111111111';
    $result = $this->serveForCache(
      isPublic: true,
      ownerId: $ownerId,
      viewerId: $ownerId,
    );

    $this->assertEquals(CachePolicy::privateImmutable(), $result->cachePolicy);
  }

  #[Test]
  public function itSelectsPrivateImmutableCacheForOwnerViewingOwnPrivateImage(): void {
    $ownerId = '11111111-1111-1111-1111-111111111111';
    $result = $this->serveForCache(
      isPublic: false,
      ownerId: $ownerId,
      viewerId: $ownerId,
    );

    $this->assertEquals(CachePolicy::privateImmutable(), $result->cachePolicy);
  }

  #[Test]
  public function itSelectsRevocableCacheForAuthenticatedNonOwner(): void {
    $result = $this->serveForCache(
      isPublic: false,
      ownerId: '11111111-1111-1111-1111-111111111111',
      viewerId: '22222222-2222-2222-2222-222222222222',
    );

    $this->assertEquals(CachePolicy::revocable(), $result->cachePolicy);
  }

  private function serveForCache(
    bool $isPublic,
    ?string $ownerId = null,
    ?string $viewerId = null,
  ): Item {
    $fileName = 'cache-test';

    $user = null;
    if ($ownerId !== null) {
      $user = $this->createStub(UserView::class);
      $user->method('getUuid')->willReturn($ownerId);
    }

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn((string) $fileName);
    $image->method('getMimeType')->willReturn('image/jpeg');
    $image->method('getUser')->willReturn($user);
    $image->method('getAttributes')->willReturn(ImageAttributes::create((string) $fileName, '', $isPublic));
    $image
      ->method('isOwnedBy')
      ->willReturnCallback(fn (?ID $userId): bool => $ownerId !== null && $userId?->toString() === $ownerId);

    $this->repository->method('oneById')->willReturn($image);

    return $this->createHandler()(new GetImageContentQuery()->withImageId($fileName)->withUserId($viewerId));
  }
}
