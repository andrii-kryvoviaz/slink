<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Application\Query\GetImageContent;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Slink\Image\Application\Query\GetImageContent\GetImageContentHandler;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final class GetImageContentHandlerTest extends TestCase {
  private StorageInterface&MockObject $storage;
  
  /**
   * @throws Exception
   */
  public function setUp(): void {
    parent::setUp();
    
    $this->storage = $this->createMock(StorageInterface::class);
  }
  
  /**
   * @throws NotFoundException
   * @throws Exception
   */
  #[Test]
  public function itHandlesGetImageContentQuery(): void {
    $query = $this->createMock(GetImageContentQuery::class);
    $imageOptions = $this->createMock(ImageOptions::class);
    $query->method('getImageOptions')->willReturn($imageOptions);
    
    $imageContent = 'image';
    
    $this->storage->expects($this->once())->method('getImage')->with($imageOptions)->willReturn($imageContent);
    
    $handler = new GetImageContentHandler($this->storage);
    
    $result = $handler($query);
    
    $this->assertInstanceOf(Item::class, $result);
    $this->assertEquals($imageContent, $result->resource);
  }
  
  /**
   * @throws Exception
   */
  #[Test]
  public function itThrowsNotFoundExceptionWhenImageIsNotFound(): void {
    $query = $this->createMock(GetImageContentQuery::class);
    $query->method('getImageOptions')->willReturn(null);
    
    $this->expectException(NotFoundException::class);
    
    $handler = new GetImageContentHandler($this->storage);
    $handler($query);
  }
}
