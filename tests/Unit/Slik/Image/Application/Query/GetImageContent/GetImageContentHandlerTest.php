<?php

declare(strict_types=1);

namespace Unit\Slik\Image\Application\Query\GetImageContent;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Slik\Image\Application\Query\GetImageContent\GetImageContentHandler;
use PHPUnit\Framework\TestCase;
use Slik\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slik\Shared\Application\Http\Item;
use Slik\Shared\Domain\ValueObject\ImageOptions;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
use Slik\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

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
