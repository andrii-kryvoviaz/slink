<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Domain\ValueObject\ID;

final class ImageTest extends TestCase {
    #[Test]
    public function itCreatesImage(): void {
        $id = ID::generate();
        $userId = ID::generate();
        $attributes = ImageAttributes::create('test.jpg', 'Test description', true);
        $metadata = new ImageMetadata(1024, 'image/jpeg', 800, 600);

        $image = Image::create($id, $userId, $attributes, $metadata);

        $this->assertEquals($id, $image->aggregateRootId());
        $this->assertEquals($userId, $image->getUserId());
        $this->assertEquals($attributes, $image->getAttributes());
        $this->assertEquals($metadata, $image->getMetadata());
        $this->assertFalse($image->isDeleted());
    }

    #[Test]
    public function itUpdatesAttributes(): void {
        $image = $this->createTestImage();
        $newAttributes = ImageAttributes::create('updated.jpg', 'Updated description', false);

        $image->updateAttributes($newAttributes);

        $this->assertEquals($newAttributes, $image->getAttributes());
    }

    #[Test]
    public function itAddsView(): void {
        $image = $this->createTestImage();
        $originalViews = $image->getAttributes()->getViews();

        $image->addView();

        $this->assertEquals(($originalViews ?? 0) + 1, $image->getAttributes()->getViews());
    }

    #[Test]
    public function itDeletesImage(): void {
        $image = $this->createTestImage();

        $image->delete();

        $this->assertTrue($image->isDeleted());
    }

    #[Test]
    public function itDeletesImageWithPreserveFlag(): void {
        $image = $this->createTestImage();

        $image->delete(true);

        $this->assertTrue($image->isDeleted());
    }

    #[Test]
    public function itChecksOwnership(): void {
        $userId = ID::generate();
        $otherUserId = ID::generate();
        $image = $this->createTestImage($userId);

        $this->assertTrue($image->isOwedBy($userId));
        $this->assertFalse($image->isOwedBy($otherUserId));
    }

    #[Test]
    public function itChecksFileExtension(): void {
        $image = $this->createTestImage();

        $this->assertTrue($image->hasExtension('jpg'));
        $this->assertFalse($image->hasExtension('png'));
    }

    #[Test]
    public function itGetsAttributes(): void {
        $attributes = ImageAttributes::create('test.jpg', 'Test description', true);
        $image = $this->createTestImage(attributes: $attributes);

        $result = $image->getAttributes();

        $this->assertEquals($attributes, $result);
    }

    #[Test]
    public function itGetsMetadata(): void {
        $metadata = new ImageMetadata(1024, 'image/jpeg', 800, 600);
        $image = $this->createTestImage(metadata: $metadata);

        $result = $image->getMetadata();

        $this->assertEquals($metadata, $result);
    }

    #[Test]
    public function itSetsAttributes(): void {
        $image = $this->createTestImage();
        $newAttributes = ImageAttributes::create('new.jpg', 'New description', false);

        $image->setAttributes($newAttributes);

        $this->assertEquals($newAttributes, $image->getAttributes());
    }

    #[Test]
    public function itSetsMetadata(): void {
        $image = $this->createTestImage();
        $newMetadata = new ImageMetadata(2048, 'image/png', 1024, 768);

        $image->setMetadata($newMetadata);

        $this->assertEquals($newMetadata, $image->getMetadata());
    }

    #[Test]
    public function itSetsUserId(): void {
        $image = $this->createTestImage();
        $newUserId = ID::generate();

        $image->setUserId($newUserId);

        $this->assertEquals($newUserId, $image->getUserId());
    }

    private function createTestImage(
        ?ID $userId = null,
        ?ImageAttributes $attributes = null,
        ?ImageMetadata $metadata = null
    ): Image {
        $id = ID::generate();
        $userId = $userId ?? ID::generate();
        $attributes = $attributes ?? ImageAttributes::create('test.jpg', 'Test description', true);
        $metadata = $metadata ?? new ImageMetadata(1024, 'image/jpeg', 800, 600);

        return Image::create($id, $userId, $attributes, $metadata);
    }
}
