<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Domain\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Exception\DuplicateImageException;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class DuplicateImageExceptionTest extends TestCase {
  #[Test]
  public function itFormatsUploadDateCorrectly(): void {
    $imageView = $this->createImageView(null, 'date-test.jpg');

    $exception = new DuplicateImageException($imageView);

    $message = $exception->getMessage();
    
    $this->assertStringContainsString('test-uuid-123', $message);
    $this->assertStringContainsString('Image already exists', $message);
    
    $uploadedAt = $exception->getUploadedAt();
    $this->assertNotEmpty($uploadedAt);
    
    $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $uploadedAt);
  }

  #[Test]
  public function itProvidesExistingImageAccess(): void {
    $user = $this->createMockUser('testuser');
    $imageView = $this->createImageView($user, 'access-test.jpg');

    $exception = new DuplicateImageException($imageView);

    $this->assertSame($imageView, $exception->getExistingImage());
  }

  #[Test]
  public function itProvidesCorrectProperty(): void {
    $imageView = $this->createImageView(null, 'property-test.jpg');

    $exception = new DuplicateImageException($imageView);

    $this->assertEquals('duplicate_image', $exception->getProperty());
  }

  #[Test]
  public function itProvidesCustomDataForFrontend(): void {
    $imageView = $this->createImageView(null, 'custom-data-test.jpg');
    $exception = new DuplicateImageException($imageView);
    
    $customData = $exception->toPayload();
    
    $this->assertArrayHasKey('imageId', $customData);
    $this->assertArrayHasKey('uploadedAt', $customData);
    $this->assertArrayHasKey('existingImageUrl', $customData);
    $this->assertEquals($imageView->getUuid(), $customData['imageId']);
    $this->assertStringContainsString('/info/', $customData['existingImageUrl']);
  }

  #[Test]
  public function itProvidesUploadedAtInCorrectFormat(): void {
    $imageView = $this->createImageView(null, 'date-test.jpg');
    $exception = new DuplicateImageException($imageView);
    
    $customData = $exception->toPayload();
    $uploadedAt = $customData['uploadedAt'];
    
    // Should be ISO 8601 format
    $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $uploadedAt);
  }

  private function createImageView(?UserView $user, string $fileName, ?string $sha1Hash = 'default_hash'): ImageView {
    $attributes = ImageAttributes::create($fileName, 'Test description', true);
    $metadata = new ImageMetadata(1024, 'image/jpeg', 800, 600, $sha1Hash);

    return new ImageView(
      'test-uuid-123',
      $user,
      $attributes,
      $metadata
    );
  }

  private function createMockUser(string $username): UserView {
    $user = $this->createStub(UserView::class);
    $user->method('getUsername')->willReturn($username);
    return $user;
  }
}