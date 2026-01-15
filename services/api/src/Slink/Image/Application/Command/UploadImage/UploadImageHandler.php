<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UploadImage;

use Slink\Image\Application\Service\ImageConversionResolver;
use Slink\Image\Domain\Context\ImageCreationContext;
use Slink\Image\Domain\Exception\DuplicateImageException;
use Slink\Image\Domain\Factory\ImageMetadataFactory;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageConversionResolverInterface;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageFile;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Slink\User\Infrastructure\ReadModel\Repository\UserPreferencesRepository;

final readonly class UploadImageHandler implements CommandHandlerInterface {

  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
    private ImageStoreRepositoryInterface  $imageRepository,
    private ImageAnalyzerInterface         $imageAnalyzer,
    private ImageTransformerInterface      $imageTransformer,
    private ImageSanitizerInterface        $sanitizer,
    private ImageConversionResolverInterface $conversionResolver,
    private ImageCreationContext           $creationContext,
    private ImageMetadataFactory           $metadataFactory,
    private StorageInterface               $storage,
    private UserPreferencesRepository      $userPreferencesRepository,
  ) {
  }

  /**
   * @throws DateTimeException
   * @throws DuplicateImageException
   */
  public function __invoke(UploadImageCommand $command, ?string $userId = null): void {
    $file = $command->getImageFile();
    $imageId = $command->getId();

    $userId = $userId
      ? ID::fromString($userId)
      : null;

    if ($this->imageAnalyzer->requiresSanitization($file->getMimeType())) {
      $file = $this->sanitizer->sanitizeFile($file);
    }

    if ($targetFormat = $this->conversionResolver->resolve($file)) {
      $file = $this->imageTransformer->convertToFormat($file, $targetFormat);
    }

    if (
      $this->imageAnalyzer->supportsExifProfile($file->getMimeType())
      && $this->configurationProvider->get('image.stripExifMetadata')
    ) {
      $this->imageTransformer->stripExifMetadata($file->getPathname());
    }

    $fileName = sprintf('%s.%s', $imageId, $file->guessExtension());

    $imageFile = ImageFile::fromSymfonyFile($file);
    $metadata = $this->metadataFactory->createFromImageFile($imageFile);

    $isPublic = $command->isPublic();
    if ($this->configurationProvider->get('image.allowOnlyPublicImages') || $userId === null) {
      $isPublic = true;
    }

    $license = null;
    if ($userId && $this->configurationProvider->get('image.enableLicensing')) {
      $userPrefs = $this->userPreferencesRepository->findByUserId($userId->toString());
      $license = $userPrefs?->getPreferences()->getDefaultLicense();
    }

    $attributes = ImageAttributes::create(
      $fileName,
      $command->getDescription(),
      $isPublic,
    );

    $image = Image::create(
      context: $this->creationContext,
      id: $imageId,
      userId: $userId,
      attributes: $attributes,
      metadata: $metadata,
      imageFile: $imageFile,
      license: $license
    );

    $this->storage->upload($file, $fileName);

    $this->imageRepository->store($image);
  }
}