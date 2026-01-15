<?php

declare(strict_types=1);

namespace Tests\Integration\Slink\Image\Application\Command\UploadImage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Image\Application\Command\UploadImage\UploadImageHandler;
use Slink\Image\Domain\Context\ImageCreationContext;
use Slink\Image\Domain\Exception\DuplicateImageException;
use Slink\Image\Domain\Factory\ImageMetadataFactory;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageConversionResolverInterface;
use Slink\Image\Domain\Service\ImageHashCalculatorInterface;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Image\Domain\Specification\ImageDuplicateSpecification;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Slink\User\Domain\Repository\UserPreferencesRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadImageHandlerUserSpecificDeduplicationTest extends TestCase {
  private UploadImageHandler $handler;
  /** @var MockObject&ImageStoreRepositoryInterface */
  private ImageStoreRepositoryInterface $imageStoreRepository;
  /** @var MockObject&ImageRepositoryInterface */
  private ImageRepositoryInterface $imageRepository;
  /** @var MockObject&ImageHashCalculatorInterface */
  private ImageHashCalculatorInterface $hashCalculator;
  /** @var MockObject&ConfigurationProviderInterface */
  private ConfigurationProviderInterface $configurationProvider;

  protected function setUp(): void {
    $this->imageStoreRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $this->imageRepository = $this->createMock(ImageRepositoryInterface::class);
    $this->hashCalculator = $this->createMock(ImageHashCalculatorInterface::class);
    $this->configurationProvider = $this->createMock(ConfigurationProviderInterface::class);

    $imageAnalyzer = $this->createMock(ImageAnalyzerInterface::class);
    $imageTransformer = $this->createMock(ImageTransformerInterface::class);
    $sanitizer = $this->createMock(ImageSanitizerInterface::class);
    $conversionResolver = $this->createMock(ImageConversionResolverInterface::class);
    $storage = $this->createMock(StorageInterface::class);
    $metadataFactory = $this->createMock(ImageMetadataFactory::class);

    $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configurationProvider->method('get')->with('image.enableDeduplication')->willReturn(true);

    $duplicateSpecification = new ImageDuplicateSpecification($this->imageRepository, $this->hashCalculator, $configurationProvider);
    $creationContext = new ImageCreationContext($duplicateSpecification);

    $imageAnalyzer->method('isConversionRequired')->willReturn(false);
    $imageAnalyzer->method('requiresSanitization')->willReturn(false);
    $imageAnalyzer->method('supportsExifProfile')->willReturn(false);

    $conversionResolver->method('resolve')->willReturn(null);

    $this->configurationProvider->method('get')->willReturnMap([
      ['image.stripExifMetadata', false],
      ['image.allowOnlyPublicImages', false],
    ]);

    $metadataFactory->method('createFromImageFile')->willReturn(
      new ImageMetadata(1024, 'image/jpeg', 800, 600, 'test_hash')
    );

    $userPreferencesRepo = $this->createMock(UserPreferencesRepositoryInterface::class);

    $this->handler = new UploadImageHandler(
      $this->configurationProvider,
      $this->imageStoreRepository,
      $imageAnalyzer,
      $imageTransformer,
      $sanitizer,
      $conversionResolver,
      $creationContext,
      $metadataFactory,
      $storage,
      $userPreferencesRepo
    );
  }

  #[Test]
  public function itAllowsDifferentUsersToUploadSameImage(): void {
    $user1Id = ID::generate();
    $user2Id = ID::generate();

    $file = $this->createTempImageFile();

    $command1 = new UploadImageCommand($file, true, 'Test image for user 1');
    $command2 = new UploadImageCommand($file, true, 'Test image for user 2');

    $this->hashCalculator->method('calculateFromImageFile')->willReturn('same_hash');

    $this->imageRepository->method('findBySha1Hash')
      ->willReturnCallback(function (string $hash, ?ID $userId) use ($user1Id, $user2Id) {
        if ($userId !== null && $userId->equals($user1Id)) {
          return null; 
        }
        if ($userId !== null && $userId->equals($user2Id)) {
          return null; 
        }
        return null;
      });

    $this->imageStoreRepository->expects($this->exactly(2))
      ->method('store');

    ($this->handler)($command1, $user1Id->toString());
    ($this->handler)($command2, $user2Id->toString());
  }

  #[Test]
  public function itPreventsSameUserFromUploadingDuplicateImage(): void {
    $userId = ID::generate();

    $file1 = $this->createTempImageFile();
    $file2 = $this->createTempImageFile();

    $command1 = new UploadImageCommand($file1, true, 'Original image');
    $command2 = new UploadImageCommand($file2, true, 'Duplicate image');

    $this->hashCalculator->method('calculateFromImageFile')->willReturn('duplicate_hash');

    $existingImage = $this->createMockImageView($userId, 'duplicate_hash');

    $callCount = 0;
    $this->imageRepository->method('findBySha1Hash')
      ->willReturnCallback(function (string $hash, ?ID $userIdParam) use ($userId, $existingImage, &$callCount) {
        $callCount++;
        if ($callCount === 1) {
          return null; 
        }
        if ($userIdParam !== null && $userIdParam->equals($userId)) {
          return $existingImage;
        }
        return null;
      });

    $this->imageStoreRepository->expects($this->once())
      ->method('store');

    ($this->handler)($command1, $userId->toString());

    $this->expectException(DuplicateImageException::class);
    ($this->handler)($command2, $userId->toString());
  }

  private function createTempImageFile(): UploadedFile {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_image');
    file_put_contents($tempFile, 'fake image content');

    return new UploadedFile(
      $tempFile,
      'test.jpg',
      'image/jpeg',
      null,
      true
    );
  }

  private function createMockImageView(ID $userId, string $sha1Hash): ImageView {
    return $this->createMock(ImageView::class);
  }
}