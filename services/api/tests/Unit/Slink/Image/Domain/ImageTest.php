<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Context\ImageCreationContext;
use Slink\Image\Domain\Exception\DuplicateImageException;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageHashCalculatorInterface;
use Slink\Image\Domain\Specification\ImageDuplicateSpecification;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageFile;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
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
    $userId = ID::generate();
    $image = $this->createTestImage($userId);

    $image->delete($userId);

    $this->assertTrue($image->isDeleted());
  }

  #[Test]
  public function itDeletesImageWithPreserveFlag(): void {
    $userId = ID::generate();
    $image = $this->createTestImage($userId);

    $image->delete($userId, true);

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
    $metadata = new ImageMetadata(1024, 'image/jpeg', 800, 600, 'test_hash');
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
    $newMetadata = new ImageMetadata(2048, 'image/png', 1024, 768, 'test_hash');

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

  #[Test]
  public function itCreatesImageWithSha1Hash(): void {
    $id = ID::generate();
    $userId = ID::generate();
    $attributes = ImageAttributes::create('test.jpg', 'Test description', true);
    $metadata = new ImageMetadata(1024, 'image/jpeg', 800, 600, 'test_sha1_hash');

    $image = Image::create($id, $userId, $attributes, $metadata);

    $this->assertEquals('test_sha1_hash', $image->getMetadata()->getSha1Hash());
  }

  #[Test]
  public function itThrowsExceptionWhenUploadingDuplicateImageForSameUser(): void {
    $userId = ID::generate();
    $imageFile = $this->createMockImageFile();
    $sha1Hash = 'duplicate_hash';

    $existingImage = $this->createMockImageView($userId, $sha1Hash);

    $repository = $this->createMock(ImageRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findBySha1Hash')
      ->with($sha1Hash, $userId)
      ->willReturn($existingImage);

    $hashCalculator = $this->createMock(ImageHashCalculatorInterface::class);
    $hashCalculator->expects($this->once())
      ->method('calculateFromImageFile')
      ->with($imageFile)
      ->willReturn($sha1Hash);

    $configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configurationProvider->method('get')->with('image.enableDeduplication')->willReturn(true);

    $duplicateSpecification = new ImageDuplicateSpecification($repository, $hashCalculator, $configurationProvider);
    $context = new ImageCreationContext($duplicateSpecification);

    $this->expectException(DuplicateImageException::class);

    Image::create(
      ID::generate(),
      $userId,
      ImageAttributes::create('test.jpg', 'Test description', true),
      new ImageMetadata(1024, 'image/jpeg', 800, 600, $sha1Hash),
      $context,
      $imageFile
    );
  }

  #[Test]
  public function itAllowsUploadingSameImageForDifferentUsers(): void {
    $user1Id = ID::generate();
    $user2Id = ID::generate();
    $imageFile = $this->createMockImageFile();
    $sha1Hash = 'same_hash';

    $repository = $this->createMock(ImageRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findBySha1Hash')
      ->with($sha1Hash, $user2Id)
      ->willReturn(null);

    $hashCalculator = $this->createMock(ImageHashCalculatorInterface::class);
    $hashCalculator->expects($this->once())
      ->method('calculateFromImageFile')
      ->with($imageFile)
      ->willReturn($sha1Hash);

    $configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configurationProvider->method('get')->with('image.enableDeduplication')->willReturn(true);

    $duplicateSpecification = new ImageDuplicateSpecification($repository, $hashCalculator, $configurationProvider);
    $context = new ImageCreationContext($duplicateSpecification);

    $image = Image::create(
      ID::generate(),
      $user2Id,
      ImageAttributes::create('test.jpg', 'Test description', true),
      new ImageMetadata(1024, 'image/jpeg', 800, 600, $sha1Hash),
      $context,
      $imageFile
    );

    $this->assertEquals($user2Id, $image->getUserId());
    $this->assertEquals($sha1Hash, $image->getMetadata()->getSha1Hash());
  }

  #[Test]
  public function itAllowsUploadingImageForGuestUserEvenIfDuplicateExists(): void {
    $imageFile = $this->createMockImageFile();
    $sha1Hash = 'guest_hash';

    $repository = $this->createMock(ImageRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findBySha1Hash')
      ->with($sha1Hash, null)
      ->willReturn(null);

    $hashCalculator = $this->createMock(ImageHashCalculatorInterface::class);
    $hashCalculator->expects($this->once())
      ->method('calculateFromImageFile')
      ->with($imageFile)
      ->willReturn($sha1Hash);

    $configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configurationProvider->method('get')->with('image.enableDeduplication')->willReturn(true);

    $duplicateSpecification = new ImageDuplicateSpecification($repository, $hashCalculator, $configurationProvider);
    $context = new ImageCreationContext($duplicateSpecification);

    $image = Image::create(
      ID::generate(),
      null,
      ImageAttributes::create('test.jpg', 'Test description', true),
      new ImageMetadata(1024, 'image/jpeg', 800, 600, $sha1Hash),
      $context,
      $imageFile
    );

    $this->assertNull($image->getUserId());
    $this->assertEquals($sha1Hash, $image->getMetadata()->getSha1Hash());
  }

  private function createMockImageFile(): ImageFile {
    return new ImageFile(
      pathname: '/tmp/test.jpg',
      mimeType: 'image/jpeg',
      extension: 'jpg',
      size: 1024
    );
  }

  private function createMockImageView(ID $userId, string $sha1Hash): ImageView {
    $attributes = ImageAttributes::create('existing.jpg', 'Existing image', true);
    $metadata = new ImageMetadata(1024, 'image/jpeg', 800, 600, $sha1Hash);

    return new ImageView(
      $userId->toString(),
      null,
      $attributes,
      $metadata
    );
  }

  private function createTestImage(
    ?ID              $userId = null,
    ?ImageAttributes $attributes = null,
    ?ImageMetadata   $metadata = null
  ): Image {
    $id = ID::generate();
    $userId = $userId ?? ID::generate();
    $attributes = $attributes ?? ImageAttributes::create('test.jpg', 'Test description', true);
    $metadata = $metadata ?? new ImageMetadata(1024, 'image/jpeg', 800, 600);

    return Image::create($id, $userId, $attributes, $metadata);
  }
}
