<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Infrastructure\Persistence\EventStore;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\ID;

final class ImageSnapshotTest extends TestCase {

  #[Test]
  public function itCreatesSnapshotFromImage(): void {
    $imageId = ID::generate();
    $userId = ID::generate();
    $attributes = ImageAttributes::create('test.jpg', 'Test description', true);
    $metadata = new ImageMetadata(1024, 'image/jpeg', 800, 600);

    $image = Image::create($imageId, $userId, $attributes, $metadata);

    $reflection = new \ReflectionClass($image);
    $method = $reflection->getMethod('createSnapshotState');
    $method->setAccessible(true);
    $snapshot = $method->invoke($image);

    $this->assertEquals($userId->toString(), $snapshot['userId']);
    $this->assertEquals($attributes->toPayload(), $snapshot['attributes']);
    $this->assertEquals($metadata->toPayload(), $snapshot['metadata']);
    $this->assertFalse($snapshot['deleted']);
  }

  #[Test]
  public function itRestoresImageFromSnapshot(): void {
    $imageId = ID::generate();
    $userId = ID::generate();
    $attributesPayload = [
      'fileName' => 'test.jpg',
      'description' => 'Test description',
      'isPublic' => true,
      'createdAt' => '2025-09-16T10:00:00+00:00',
      'updatedAt' => null,
      'views' => 0,
    ];
    $metadataPayload = [
      'size' => 1024,
      'mimeType' => 'image/jpeg',
      'width' => 800,
      'height' => 600
    ];

    $snapshotState = [
      'userId' => $userId->toString(),
      'attributes' => $attributesPayload,
      'metadata' => $metadataPayload,
      'deleted' => false,
    ];

    $reflection = new \ReflectionClass(Image::class);
    $method = $reflection->getMethod('reconstituteFromSnapshotState');
    $method->setAccessible(true);
    $image = $method->invoke(null, $imageId, $snapshotState);

    $this->assertEquals($imageId, $image->aggregateRootId());
    $this->assertEquals($userId, $image->getUserId());
    $this->assertEquals('test.jpg', $image->getAttributes()->getFileName());
    $this->assertEquals('Test description', $image->getAttributes()->getDescription());
    $this->assertTrue($image->getAttributes()->isPublic());
    $this->assertEquals(1024, $image->getMetadata()->getSize());
    $this->assertEquals('image/jpeg', $image->getMetadata()->getMimeType());
    $this->assertEquals(800, $image->getMetadata()->getWidth());
    $this->assertEquals(600, $image->getMetadata()->getHeight());
    $this->assertFalse($image->isDeleted());
  }

  #[Test]
  public function itMaintainsDataIntegrityThroughSnapshotCycle(): void {
    $imageId = ID::generate();
    $userId = ID::generate();
    $attributes = ImageAttributes::create('original.png', 'Original description', false);
    $metadata = new ImageMetadata(2048, 'image/png', 1920, 1080);

    $originalImage = Image::create($imageId, $userId, $attributes, $metadata);
    
    $reflection = new \ReflectionClass($originalImage);
    $createMethod = $reflection->getMethod('createSnapshotState');
    $createMethod->setAccessible(true);
    $snapshotState = $createMethod->invoke($originalImage);
    
    $restoreMethod = $reflection->getMethod('reconstituteFromSnapshotState');
    $restoreMethod->setAccessible(true);
    $restoredImage = $restoreMethod->invoke(null, $imageId, $snapshotState);

    $this->assertEquals($originalImage->aggregateRootId(), $restoredImage->aggregateRootId());
    $this->assertEquals($originalImage->getUserId(), $restoredImage->getUserId());
    $this->assertEquals($originalImage->getAttributes()->getFilename(), $restoredImage->getAttributes()->getFilename());
    $this->assertEquals($originalImage->getAttributes()->getDescription(), $restoredImage->getAttributes()->getDescription());
    $this->assertEquals($originalImage->getAttributes()->isPublic(), $restoredImage->getAttributes()->isPublic());
    $this->assertEquals($originalImage->getMetadata()->getSize(), $restoredImage->getMetadata()->getSize());
    $this->assertEquals($originalImage->getMetadata()->getMimeType(), $restoredImage->getMetadata()->getMimeType());
    $this->assertEquals($originalImage->getMetadata()->getWidth(), $restoredImage->getMetadata()->getWidth());
    $this->assertEquals($originalImage->getMetadata()->getHeight(), $restoredImage->getMetadata()->getHeight());
    $this->assertEquals($originalImage->isDeleted(), $restoredImage->isDeleted());
  }

  #[Test]
  public function itHandlesDeletedImageInSnapshot(): void {
    $imageId = ID::generate();
    $userId = ID::generate();
    $attributes = ImageAttributes::create('deleted.jpg', 'Deleted image', true);
    $metadata = new ImageMetadata(512, 'image/jpeg', 640, 480);

    $image = Image::create($imageId, $userId, $attributes, $metadata);
    $image->delete();

    $reflection = new \ReflectionClass($image);
    $createMethod = $reflection->getMethod('createSnapshotState');
    $createMethod->setAccessible(true);
    $snapshotState = $createMethod->invoke($image);
    
    $restoreMethod = $reflection->getMethod('reconstituteFromSnapshotState');
    $restoreMethod->setAccessible(true);
    $restoredImage = $restoreMethod->invoke(null, $imageId, $snapshotState);

    $this->assertTrue($restoredImage->isDeleted());
    $this->assertEquals($image->aggregateRootId(), $restoredImage->aggregateRootId());
  }

  #[Test]
  public function itHandlesImageWithViews(): void {
    $imageId = ID::generate();
    $userId = ID::generate();
    $attributes = ImageAttributes::create('viewed.jpg', 'Popular image', true);
    $metadata = new ImageMetadata(1536, 'image/jpeg', 1024, 768);

    $image = Image::create($imageId, $userId, $attributes, $metadata);
    
    $image->addView();
    $image->addView();

    $reflection = new \ReflectionClass($image);
    $createMethod = $reflection->getMethod('createSnapshotState');
    $createMethod->setAccessible(true);
    $snapshotState = $createMethod->invoke($image);
    
    $restoreMethod = $reflection->getMethod('reconstituteFromSnapshotState');
    $restoreMethod->setAccessible(true);
    $restoredImage = $restoreMethod->invoke(null, $imageId, $snapshotState);

    $this->assertEquals($image->aggregateRootId(), $restoredImage->aggregateRootId());
    $this->assertEquals($image->getAttributes()->getViews(), $restoredImage->getAttributes()->getViews());
  }

  #[Test]
  public function itHandlesUpdatedAttributes(): void {
    $imageId = ID::generate();
    $userId = ID::generate();
    $originalAttributes = ImageAttributes::create('original.jpg', 'Original', true);
    $metadata = new ImageMetadata(1024, 'image/jpeg', 800, 600);

    $image = Image::create($imageId, $userId, $originalAttributes, $metadata);
    
    $updatedAttributes = ImageAttributes::create('updated.jpg', 'Updated description', false);
    $image->updateAttributes($updatedAttributes);

    $reflection = new \ReflectionClass($image);
    $createMethod = $reflection->getMethod('createSnapshotState');
    $createMethod->setAccessible(true);
    $snapshotState = $createMethod->invoke($image);
    
    $restoreMethod = $reflection->getMethod('reconstituteFromSnapshotState');
    $restoreMethod->setAccessible(true);
    $restoredImage = $restoreMethod->invoke(null, $imageId, $snapshotState);

    $this->assertEquals('updated.jpg', $restoredImage->getAttributes()->getFilename());
    $this->assertEquals('Updated description', $restoredImage->getAttributes()->getDescription());
    $this->assertFalse($restoredImage->getAttributes()->isPublic());
  }
}