<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Domain\Specification;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Exception\DuplicateImageException;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageHashCalculatorInterface;
use Slink\Image\Domain\Specification\ImageDuplicateSpecification;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageFile;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;

final class ImageDuplicateSpecificationTest extends TestCase {
  #[Test]
  public function itThrowsExceptionWhenDuplicateFoundForSameUser(): void {
    $userId = ID::generate();
    $sha1Hash = 'test_hash';
    $imageFile = $this->createMockImageFile();

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

    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configurationProvider->expects($this->once())
      ->method('get')
      ->with('image.enableDeduplication')
      ->willReturn(true);

    $specification = new ImageDuplicateSpecification($repository, $hashCalculator, $configurationProvider);

    $this->expectException(DuplicateImageException::class);

    $specification->ensureNoDuplicate($imageFile, $userId);
  }

  #[Test]
  public function itAllowsUploadWhenNoDuplicateFoundForSameUser(): void {
    $userId = ID::generate();
    $sha1Hash = 'test_hash';
    $imageFile = $this->createMockImageFile();

    $repository = $this->createMock(ImageRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findBySha1Hash')
      ->with($sha1Hash, $userId)
      ->willReturn(null);

    $hashCalculator = $this->createMock(ImageHashCalculatorInterface::class);
    $hashCalculator->expects($this->once())
      ->method('calculateFromImageFile')
      ->with($imageFile)
      ->willReturn($sha1Hash);

    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configurationProvider->expects($this->once())
      ->method('get')
      ->with('image.enableDeduplication')
      ->willReturn(true);

    $specification = new ImageDuplicateSpecification($repository, $hashCalculator, $configurationProvider);

    $specification->ensureNoDuplicate($imageFile, $userId);

    // Test passes if no exception is thrown
  }

  #[Test]
  public function itAllowsUploadForDifferentUserEvenWithSameHash(): void {
    $userId1 = ID::generate();
    $userId2 = ID::generate();
    $sha1Hash = 'test_hash';
    $imageFile = $this->createMockImageFile();

    $repository = $this->createMock(ImageRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findBySha1Hash')
      ->with($sha1Hash, $userId2)
      ->willReturn(null);

    $hashCalculator = $this->createMock(ImageHashCalculatorInterface::class);
    $hashCalculator->expects($this->once())
      ->method('calculateFromImageFile')
      ->with($imageFile)
      ->willReturn($sha1Hash);

    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configurationProvider->expects($this->once())
      ->method('get')
      ->with('image.enableDeduplication')
      ->willReturn(true);

    $specification = new ImageDuplicateSpecification($repository, $hashCalculator, $configurationProvider);

    $specification->ensureNoDuplicate($imageFile, $userId2);
  }

  #[Test]
  public function itAllowsUploadForGuestUserEvenWithSameHash(): void {
    $sha1Hash = 'test_hash';
    $imageFile = $this->createMockImageFile();

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

    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configurationProvider->expects($this->once())
      ->method('get')
      ->with('image.enableDeduplication')
      ->willReturn(true);

    $specification = new ImageDuplicateSpecification($repository, $hashCalculator, $configurationProvider);

    $specification->ensureNoDuplicate($imageFile, null);
  }

  #[Test]
  public function itHandlesEmptyUserIdCorrectly(): void {
    $sha1Hash = 'test_hash';
    $imageFile = $this->createMockImageFile();

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

    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configurationProvider->expects($this->once())
      ->method('get')
      ->with('image.enableDeduplication')
      ->willReturn(true);

    $specification = new ImageDuplicateSpecification($repository, $hashCalculator, $configurationProvider);

    $specification->ensureNoDuplicate($imageFile);
  }

  #[Test]
  public function itSkipsDuplicateCheckWhenDeduplicationIsDisabled(): void {
    $userId = ID::generate();
    $sha1Hash = 'test_hash';
    $imageFile = $this->createMockImageFile();

    $repository = $this->createMock(ImageRepositoryInterface::class);
    $repository->expects($this->never())
      ->method('findBySha1Hash');

    $hashCalculator = $this->createMock(ImageHashCalculatorInterface::class);
    $hashCalculator->expects($this->once())
      ->method('calculateFromImageFile')
      ->with($imageFile)
      ->willReturn($sha1Hash);

    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configurationProvider->expects($this->once())
      ->method('get')
      ->with('image.enableDeduplication')
      ->willReturn(false);

    $specification = new ImageDuplicateSpecification($repository, $hashCalculator, $configurationProvider);

    $specification->ensureNoDuplicate($imageFile, $userId);
  }

  #[Test]
  public function itAllowsDuplicateUploadWhenDeduplicationIsDisabled(): void {
    $userId = ID::generate();
    $sha1Hash = 'test_hash';
    $imageFile = $this->createMockImageFile();

    $repository = $this->createMock(ImageRepositoryInterface::class);
    $repository->expects($this->never())
      ->method('findBySha1Hash');

    $hashCalculator = $this->createMock(ImageHashCalculatorInterface::class);
    $hashCalculator->expects($this->once())
      ->method('calculateFromImageFile')
      ->with($imageFile)
      ->willReturn($sha1Hash);

    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configurationProvider->expects($this->once())
      ->method('get')
      ->with('image.enableDeduplication')
      ->willReturn(false);

    $specification = new ImageDuplicateSpecification($repository, $hashCalculator, $configurationProvider);

    $specification->ensureNoDuplicate($imageFile, $userId);
  }

  #[Test]
  public function itDefaultsToEnabledWhenConfigurationIsNull(): void {
    $userId = ID::generate();
    $sha1Hash = 'test_hash';
    $imageFile = $this->createMockImageFile();

    $repository = $this->createMock(ImageRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findBySha1Hash')
      ->with($sha1Hash, $userId)
      ->willReturn(null);

    $hashCalculator = $this->createMock(ImageHashCalculatorInterface::class);
    $hashCalculator->expects($this->once())
      ->method('calculateFromImageFile')
      ->with($imageFile)
      ->willReturn($sha1Hash);

    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configurationProvider->expects($this->once())
      ->method('get')
      ->with('image.enableDeduplication')
      ->willReturn(null);

    $specification = new ImageDuplicateSpecification($repository, $hashCalculator, $configurationProvider);

    $specification->ensureNoDuplicate($imageFile, $userId);
  }

  #[Test]
  public function itAlwaysCalculatesHashRegardlessOfDeduplicationSetting(): void {
    $userId = ID::generate();
    $imageFile = $this->createMockImageFile();

    $repository = $this->createMock(ImageRepositoryInterface::class);
    $repository->expects($this->never())
      ->method('findBySha1Hash');

    $hashCalculator = $this->createMock(ImageHashCalculatorInterface::class);
    $hashCalculator->expects($this->once())
      ->method('calculateFromImageFile')
      ->with($imageFile)
      ->willReturn('test_hash');

    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configurationProvider->expects($this->once())
      ->method('get')
      ->with('image.enableDeduplication')
      ->willReturn(false);

    $specification = new ImageDuplicateSpecification($repository, $hashCalculator, $configurationProvider);

    $specification->ensureNoDuplicate($imageFile, $userId);
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
}