<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Application\Query\GetImageContent;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetImageContent\GetImageContentHandler;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

final class GetImageContentHandlerTest extends TestCase {
  private StorageInterface&MockObject $storage;
  private ImageRepositoryInterface&MockObject $repository;
  private ImageAnalyzerInterface&MockObject $imageAnalyzer;
  private ImageSanitizerInterface&MockObject $sanitizer;
  
  /**
   * @throws Exception
   */
  public function setUp(): void {
    parent::setUp();
    
    $this->storage = $this->createMock(StorageInterface::class);
    $this->repository = $this->createMock(ImageRepositoryInterface::class);
    $this->imageAnalyzer = $this->createMock(ImageAnalyzerInterface::class);
    $this->sanitizer = $this->createMock(ImageSanitizerInterface::class);
  }
  
  /**
   * @throws NotFoundException
   * @throws Exception
   */
  #[Test]
  public function itHandlesGetImageContentQuery(): void {
    $imageId = 'test-file-name';
    $fileName = 'test-file-name.jpg';
    $imageContent = 'image content';
    $mimeType = 'image/jpeg';
    
    $image = $this->createMock(ImageView::class);
    $image->method('getAttributes')->willReturn(ImageAttributes::create($imageId, 'description', true));
    $image->method('getMimeType')->willReturn($mimeType);
    $image->method('getFileName')->willReturn($fileName);
    
    $this->repository->method('oneById')->with($imageId)->willReturn($image);
    $this->imageAnalyzer->method('supportsResize')->with($mimeType)->willReturn(true);
    $this->storage->method('getImage')->willReturn($imageContent);
    $this->sanitizer->method('requiresSanitization')->with($mimeType)->willReturn(false);
    
    $handler = new GetImageContentHandler($this->imageAnalyzer, $this->repository, $this->storage, $this->sanitizer);
    $result = ($handler)(new GetImageContentQuery(), $fileName);
    
    $this->assertInstanceOf(Item::class, $result);
    $this->assertEquals($imageContent, $result->resource);
    $this->assertEquals($mimeType, $result->type);
  }
  
  /**
   * @throws Exception
   */
  #[Test]
  public function itThrowsNotFoundExceptionWhenImageIsNotFound(): void {
    $imageId = 'test-file-name';
    $fileName = 'test-file-name.jpg';
    
    $this->repository->method('oneById')->with($imageId)->willThrowException(new NotFoundException());
    
    $this->expectException(NotFoundException::class);
    
    $handler = new GetImageContentHandler($this->imageAnalyzer, $this->repository, $this->storage, $this->sanitizer);
    $handler(new GetImageContentQuery(), $fileName);
  }
  
  /**
   * @throws NotFoundException
   * @throws Exception
   */
  #[Test]
  public function itSanitizesSvgContentWhenServing(): void {
    $imageId = 'test-file-name';
    $fileName = 'test-file-name.svg';
    $originalSvgContent = '<svg><script>alert("xss")</script><rect/></svg>';
    $sanitizedSvgContent = '<svg><rect/></svg>';
    $mimeType = 'image/svg+xml';
    
    $image = $this->createMock(ImageView::class);
    $image->method('getAttributes')->willReturn(ImageAttributes::create($imageId, 'description', true));
    $image->method('getMimeType')->willReturn($mimeType);
    $image->method('getFileName')->willReturn($fileName);
    
    $this->repository->method('oneById')->with($imageId)->willReturn($image);
    $this->imageAnalyzer->method('supportsResize')->with($mimeType)->willReturn(false);
    $this->storage->method('getImage')->willReturn($originalSvgContent);
    $this->sanitizer->method('requiresSanitization')->with($mimeType)->willReturn(true);
    $this->sanitizer->method('sanitize')->with($originalSvgContent)->willReturn($sanitizedSvgContent);
    
    $handler = new GetImageContentHandler($this->imageAnalyzer, $this->repository, $this->storage, $this->sanitizer);
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
    
    $image = $this->createMock(ImageView::class);
    $image->method('getMimeType')->willReturn($originalMimeType);
    $image->method('getFileName')->willReturn($originalFileName);
    
    $this->repository->method('oneById')->with($imageId)->willReturn($image);
    $this->imageAnalyzer->method('supportsResize')->with($originalMimeType)->willReturn(true);
    $this->storage->method('getImage')->willReturn($imageContent);
    $this->sanitizer->method('requiresSanitization')->with($originalMimeType)->willReturn(false);
    
    $handler = new GetImageContentHandler($this->imageAnalyzer, $this->repository, $this->storage, $this->sanitizer);
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
    
    $image = $this->createMock(ImageView::class);
    $image->method('getMimeType')->willReturn($mimeType);
    $image->method('getFileName')->willReturn($fileName);
    
    $this->repository->method('oneById')->with($imageId)->willReturn($image);
    $this->imageAnalyzer->method('supportsResize')->with($mimeType)->willReturn(true);
    $this->storage->method('getImage')->willReturn($imageContent);
    $this->sanitizer->method('requiresSanitization')->with($mimeType)->willReturn(false);
    
    $handler = new GetImageContentHandler($this->imageAnalyzer, $this->repository, $this->storage, $this->sanitizer);
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
    
    $image = $this->createMock(ImageView::class);
    $image->method('getMimeType')->willReturn($mimeType);
    $image->method('getFileName')->willReturn($fileName);
    
    $this->repository->method('oneById')->with($imageId)->willReturn($image);
    $this->imageAnalyzer->method('supportsResize')->with($mimeType)->willReturn(true);
    $this->storage->method('getImage')->willReturn($imageContent);
    $this->sanitizer->method('requiresSanitization')->with($mimeType)->willReturn(false);
    
    $handler = new GetImageContentHandler($this->imageAnalyzer, $this->repository, $this->storage, $this->sanitizer);
    $result = ($handler)(new GetImageContentQuery(), 'test-file-name.jpg', 'jpg');
    
    $this->assertInstanceOf(Item::class, $result);
    $this->assertEquals($imageContent, $result->resource);
    $this->assertEquals($mimeType, $result->type);
  }
}
