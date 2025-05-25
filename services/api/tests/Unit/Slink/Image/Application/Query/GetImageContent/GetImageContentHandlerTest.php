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
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

final class GetImageContentHandlerTest extends TestCase {
  private StorageInterface&MockObject $storage;
  private ImageRepositoryInterface&MockObject $repository;
  private ImageAnalyzerInterface&MockObject $imageAnalyzer;
  
  /**
   * @throws Exception
   */
  public function setUp(): void {
    parent::setUp();
    
    $this->storage = $this->createMock(StorageInterface::class);
    $this->repository = $this->createMock(ImageRepositoryInterface::class);
    $this->imageAnalyzer = $this->createMock(ImageAnalyzerInterface::class);
  }
  
  /**
   * @throws NotFoundException
   * @throws Exception
   */
  #[Test]
  public function itHandlesGetImageContentQuery(): void {
    $fileName = 'test-file-name.jpg';
    $imageContent = 'image content';
    $mimeType = 'image/jpeg';
    
    $image = $this->createMock(ImageView::class);
    $image->method('getAttributes')->willReturn(ImageAttributes::create('test-file-name', 'description', true));
    $image->method('getMimeType')->willReturn($mimeType);
    
    $this->repository->method('oneByFileName')->willReturn($image);
    $this->storage->method('getImage')->willReturn($imageContent);
    
    $handler = new GetImageContentHandler($this->imageAnalyzer, $this->repository, $this->storage);
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
    $fileName = 'test-file-name.jpg';
    
    $query = $this->createMock(GetImageContentQuery::class);
    
    $this->expectException(NotFoundException::class);
    
    $handler = new GetImageContentHandler($this->imageAnalyzer, $this->repository, $this->storage);
    $handler($query, $fileName);
  }
}
