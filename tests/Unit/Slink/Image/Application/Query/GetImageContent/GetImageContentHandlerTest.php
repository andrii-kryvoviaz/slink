<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Application\Query\GetImageContent;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Slink\Image\Application\Query\GetImageContent\GetImageContentHandler;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final class GetImageContentHandlerTest extends TestCase {
  private StorageInterface&MockObject $storage;
  private ImageStoreRepositoryInterface&MockObject $store;
  
  /**
   * @throws Exception
   */
  public function setUp(): void {
    parent::setUp();
    
    $this->storage = $this->createMock(StorageInterface::class);
    $this->store = $this->createMock(ImageStoreRepositoryInterface::class);
  }
  
  /**
   * @throws NotFoundException
   * @throws Exception
   */
  #[Test]
  public function itHandlesGetImageContentQuery(): void {
    $id = 'test-id';
    $ext = 'jpg';
    $imageContent = 'image content';
    $mimeType = 'image/jpeg';
    
    $image = $this->createMock(Image::class);
    $image->method('hasExtension')->willReturn(true);
    $image->method('getAttributes')->willReturn(ImageAttributes::create('test-file-name', 'description', true));
    $image->method('getMetadata')->willReturn(new ImageMetadata(100, $mimeType, 100, 100));
    
    $this->store->method('get')->willReturn($image);
    $this->storage->method('getImage')->willReturn($imageContent);
    
    $handler = new GetImageContentHandler($this->store, $this->storage);
    $result = ($handler)(new GetImageContentQuery(), $id, $ext);
    
    $this->assertInstanceOf(Item::class, $result);
    $this->assertEquals($imageContent, $result->resource);
    $this->assertEquals($mimeType, $result->type);
  }
  
  /**
   * @throws Exception
   */
  #[Test]
  public function itThrowsNotFoundExceptionWhenImageIsNotFound(): void {
    $id = 'test-id';
    $ext = 'jpg';
    
    $query = $this->createMock(GetImageContentQuery::class);
    
    $this->expectException(NotFoundException::class);
    
    $handler = new GetImageContentHandler($this->store, $this->storage);
    $handler($query, $id, $ext);
  }
}
