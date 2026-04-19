<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Application\Query\GetImageContent;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetImageContent\GetImageContentHandler;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageRetrievalInterface;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Slink\Image\Domain\Service\ImageUrlSignatureInterface;
use Slink\Image\Domain\ValueObject\ImageAccessContext;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class GetImageContentHandlerTest extends TestCase {
  private ImageRetrievalInterface&Stub $imageRetrieval;
  private ImageRepositoryInterface&Stub $repository;
  private ImageAnalyzerInterface&Stub $imageAnalyzer;
  private ImageSanitizerInterface&Stub $sanitizer;
  private ImageUrlSignatureInterface&Stub $transformSignature;
  private AuthorizationCheckerInterface&Stub $access;
  private ShareUrlBuilderInterface&Stub $shareUrlBuilder;

  public function setUp(): void {
    parent::setUp();

    $this->imageRetrieval = $this->createStub(ImageRetrievalInterface::class);
    $this->repository = $this->createStub(ImageRepositoryInterface::class);
    $this->imageAnalyzer = $this->createStub(ImageAnalyzerInterface::class);
    $this->sanitizer = $this->createStub(ImageSanitizerInterface::class);
    $this->transformSignature = $this->createStub(ImageUrlSignatureInterface::class);
    $this->access = $this->createStub(AuthorizationCheckerInterface::class);
    $this->shareUrlBuilder = $this->createStub(ShareUrlBuilderInterface::class);

    $this->transformSignature->method('verify')->willReturn(true);
    $this->access->method('isGranted')->willReturn(true);
    $this->shareUrlBuilder->method('buildTargetPath')->willReturn(TargetPath::fromString('/image/test.jpg'));
  }

  private function createHandler(): GetImageContentHandler {
    return new GetImageContentHandler(
      $this->imageAnalyzer,
      $this->repository,
      $this->imageRetrieval,
      $this->sanitizer,
      $this->transformSignature,
      $this->access,
      $this->shareUrlBuilder,
    );
  }

  #[Test]
  public function itHandlesGetImageContentQuery(): void {
    $imageId = 'test-file-name';
    $fileName = 'test-file-name.jpg';
    $imageContent = 'image content';
    $mimeType = 'image/jpeg';

    $image = $this->createStub(ImageView::class);
    $image->method('getAttributes')->willReturn(ImageAttributes::create($imageId, 'description', true));
    $image->method('getMimeType')->willReturn($mimeType);
    $image->method('getFileName')->willReturn($fileName);

    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $image],
    ]);
    $this->imageAnalyzer->method('supportsResize')->willReturnMap([
      [$mimeType, true],
    ]);
    $this->imageRetrieval->method('getImage')->willReturn($imageContent);
    $this->sanitizer->method('requiresSanitization')->willReturnMap([
      [$mimeType, false],
    ]);

    $handler = $this->createHandler();
    $result = ($handler)(new GetImageContentQuery(), $fileName);

    $this->assertInstanceOf(Item::class, $result);
    $this->assertEquals($imageContent, $result->resource);
    $this->assertEquals($mimeType, $result->type);
  }

  #[Test]
  public function itThrowsNotFoundExceptionWhenImageIsNotFound(): void {
    $imageId = 'test-file-name';
    $fileName = 'test-file-name.jpg';

    $this->repository->method('oneById')->willReturnCallback(function (string $id) use ($imageId): never {
      if ($id === $imageId) {
        throw new NotFoundException();
      }
      throw new \LogicException('Unexpected id: ' . $id);
    });

    $this->expectException(NotFoundException::class);

    $handler = $this->createHandler();
    $handler(new GetImageContentQuery(), $fileName);
  }

  #[Test]
  public function itSanitizesSvgContentWhenServing(): void {
    $imageId = 'test-file-name';
    $fileName = 'test-file-name.svg';
    $originalSvgContent = '<svg><script>alert("xss")</script><rect/></svg>';
    $sanitizedSvgContent = '<svg><rect/></svg>';
    $mimeType = 'image/svg+xml';

    $image = $this->createStub(ImageView::class);
    $image->method('getAttributes')->willReturn(ImageAttributes::create($imageId, 'description', true));
    $image->method('getMimeType')->willReturn($mimeType);
    $image->method('getFileName')->willReturn($fileName);

    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $image],
    ]);
    $this->imageAnalyzer->method('supportsResize')->willReturnMap([
      [$mimeType, false],
    ]);
    $this->imageRetrieval->method('getImage')->willReturn($originalSvgContent);
    $this->sanitizer->method('requiresSanitization')->willReturnMap([
      [$mimeType, true],
    ]);
    $this->sanitizer->method('sanitize')->willReturnMap([
      [$originalSvgContent, $sanitizedSvgContent],
    ]);

    $handler = $this->createHandler();
    $result = ($handler)(new GetImageContentQuery(), $fileName);

    $this->assertInstanceOf(Item::class, $result);
    $this->assertEquals($sanitizedSvgContent, $result->resource);
    $this->assertEquals($mimeType, $result->type);
  }

  #[Test]
  public function itConvertsImageFormatWhenRequested(): void {
    $imageId = 'test-file-name';
    $originalFileName = 'test-file-name.png';
    $imageContent = 'converted image content';
    $originalMimeType = 'image/png';
    $targetMimeType = 'image/webp';

    $image = $this->createStub(ImageView::class);
    $image->method('getMimeType')->willReturn($originalMimeType);
    $image->method('getFileName')->willReturn($originalFileName);

    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $image],
    ]);
    $this->imageAnalyzer->method('supportsResize')->willReturnMap([
      [$originalMimeType, true],
    ]);
    $this->imageRetrieval->method('getImage')->willReturn($imageContent);
    $this->sanitizer->method('requiresSanitization')->willReturnMap([
      [$originalMimeType, false],
    ]);

    $handler = $this->createHandler();
    $result = ($handler)(new GetImageContentQuery(), 'test-file-name.webp', 'webp');

    $this->assertInstanceOf(Item::class, $result);
    $this->assertEquals($imageContent, $result->resource);
    $this->assertEquals($targetMimeType, $result->type);
  }

  #[Test]
  public function itDoesNotConvertWhenRequestedFormatMatchesOriginal(): void {
    $imageId = 'test-file-name';
    $fileName = 'test-file-name.gif';
    $imageContent = 'animated gif content';
    $mimeType = 'image/gif';

    $image = $this->createStub(ImageView::class);
    $image->method('getMimeType')->willReturn($mimeType);
    $image->method('getFileName')->willReturn($fileName);

    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $image],
    ]);
    $this->imageAnalyzer->method('supportsResize')->willReturnMap([
      [$mimeType, true],
    ]);
    $this->imageRetrieval->method('getImage')->willReturn($imageContent);
    $this->sanitizer->method('requiresSanitization')->willReturnMap([
      [$mimeType, false],
    ]);

    $handler = $this->createHandler();
    $result = ($handler)(new GetImageContentQuery(), $fileName, 'gif');

    $this->assertInstanceOf(Item::class, $result);
    $this->assertEquals($imageContent, $result->resource);
    $this->assertEquals($mimeType, $result->type);
  }

  #[Test]
  public function itHandlesJpegJpgAliasesWithoutConversion(): void {
    $imageId = 'test-file-name';
    $fileName = 'test-file-name.jpeg';
    $imageContent = 'jpeg image content';
    $mimeType = 'image/jpeg';

    $image = $this->createStub(ImageView::class);
    $image->method('getMimeType')->willReturn($mimeType);
    $image->method('getFileName')->willReturn($fileName);

    $this->repository->method('oneById')->willReturnMap([
      [$imageId, $image],
    ]);
    $this->imageAnalyzer->method('supportsResize')->willReturnMap([
      [$mimeType, true],
    ]);
    $this->imageRetrieval->method('getImage')->willReturn($imageContent);
    $this->sanitizer->method('requiresSanitization')->willReturnMap([
      [$mimeType, false],
    ]);

    $handler = $this->createHandler();
    $result = ($handler)(new GetImageContentQuery(), 'test-file-name.jpg', 'jpg');

    $this->assertInstanceOf(Item::class, $result);
    $this->assertEquals($imageContent, $result->resource);
    $this->assertEquals($mimeType, $result->type);
  }

  #[Test]
  public function itServesImageWhenAccessGranted(): void {
    $fileName = 'test-file-name.jpg';
    $imageContent = 'image content';
    $mimeType = 'image/jpeg';

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn($fileName);
    $image->method('getMimeType')->willReturn($mimeType);
    $image->method('getUser')->willReturn(null);

    $this->repository->method('oneById')->willReturn($image);
    $this->imageAnalyzer->method('supportsResize')->willReturn(true);
    $this->imageRetrieval->method('getImage')->willReturn($imageContent);
    $this->sanitizer->method('requiresSanitization')->willReturn(false);

    $handler = $this->createHandler();
    $result = ($handler)(new GetImageContentQuery(), $fileName);

    $this->assertInstanceOf(Item::class, $result);
  }

  #[Test]
  public function itThrowsNotFoundWhenAccessDenied(): void {
    $fileName = 'test-file-name.jpg';

    $this->access = $this->createStub(AuthorizationCheckerInterface::class);
    $this->access->method('isGranted')->willReturn(false);

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn($fileName);
    $image->method('getMimeType')->willReturn('image/jpeg');
    $image->method('getUser')->willReturn(null);

    $this->repository->method('oneById')->willReturn($image);

    $this->expectException(NotFoundException::class);

    $handler = $this->createHandler();
    ($handler)(new GetImageContentQuery(), $fileName);
  }

  #[Test]
  public function itPassesScopeParamsInAccessContext(): void {
    $fileName = 'test-file-name.jpg';
    $imageContent = 'image content';
    $mimeType = 'image/jpeg';

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn($fileName);
    $image->method('getMimeType')->willReturn($mimeType);
    $image->method('getUser')->willReturn(null);

    $this->repository->method('oneById')->willReturn($image);
    $this->imageAnalyzer->method('supportsResize')->willReturn(true);
    $this->imageRetrieval->method('getImage')->willReturn($imageContent);
    $this->sanitizer->method('requiresSanitization')->willReturn(false);

    $access = $this->createMock(AuthorizationCheckerInterface::class);
    $access
      ->expects($this->once())
      ->method('isGranted')
      ->with(
        ImageAccess::View,
        $this->callback(static function (mixed $subject): bool {
          if (!$subject instanceof ImageAccessContext) {
            return false;
          }

          if ($subject->scopeCollectionId !== 'collection-id') {
            return false;
          }

          return $subject->scopeSignature === 'sig';
        }),
      )
      ->willReturn(true);

    $handler = new GetImageContentHandler(
      $this->imageAnalyzer,
      $this->repository,
      $this->imageRetrieval,
      $this->sanitizer,
      $this->transformSignature,
      $access,
      $this->shareUrlBuilder,
    );

    $query = new GetImageContentQuery(collection: 'collection-id', cs: 'sig');
    $result = ($handler)($query, $fileName);

    $this->assertInstanceOf(Item::class, $result);
  }

  #[Test]
  public function itPassesTargetPathBuiltFromSanitizedQueryInAccessContext(): void {
    $fileName = 'test-file-name.jpg';
    $imageContent = 'image content';
    $mimeType = 'image/jpeg';
    $expectedPath = TargetPath::fromString('/image/test-file-name.jpg?width=200&s=sig');

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn($fileName);
    $image->method('getMimeType')->willReturn($mimeType);
    $image->method('getUser')->willReturn(null);

    $this->repository->method('oneById')->willReturn($image);
    $this->imageAnalyzer->method('supportsResize')->willReturn(true);
    $this->imageRetrieval->method('getImage')->willReturn($imageContent);
    $this->sanitizer->method('requiresSanitization')->willReturn(false);

    $shareUrlBuilder = $this->createMock(ShareUrlBuilderInterface::class);
    $shareUrlBuilder
      ->expects($this->once())
      ->method('buildTargetPath')
      ->with('test-file-name', $fileName, 200, null, false, null, null)
      ->willReturn($expectedPath);

    $access = $this->createMock(AuthorizationCheckerInterface::class);
    $access
      ->expects($this->once())
      ->method('isGranted')
      ->with(
        ImageAccess::View,
        $this->callback(static function (mixed $subject) use ($expectedPath): bool {
          if (!$subject instanceof ImageAccessContext) {
            return false;
          }

          return $subject->targetPath?->toString() === $expectedPath->toString();
        }),
      )
      ->willReturn(true);

    $handler = new GetImageContentHandler(
      $this->imageAnalyzer,
      $this->repository,
      $this->imageRetrieval,
      $this->sanitizer,
      $this->transformSignature,
      $access,
      $shareUrlBuilder,
    );

    $query = new GetImageContentQuery(width: 200, s: 'sig');
    ($handler)($query, $fileName);
  }
}
