<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\DataMigrations;

use Doctrine\ORM\EntityManagerInterface;
use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationInterface;

final class Migration20260416032039 implements DataMigrationInterface {
  private const BATCH_SIZE = 100;

  public function __construct(
    private readonly ImageRepositoryInterface $imageRepository,
    private readonly ShareRepositoryInterface $shareRepository,
    private readonly ShareStoreRepositoryInterface $shareStore,
    private readonly ShareServiceInterface $shareService,
    private readonly ShareUrlBuilderInterface $shareUrlBuilder,
    private readonly EntityManagerInterface $entityManager,
  ) {}

  public function up(): void {
    $filter = new ImageListFilter(limit: null);
    $errors = [];
    $count = 0;

    foreach ($this->imageRepository->geImageList($filter) as $imageView) {
      try {
        $targetPath = $this->shareUrlBuilder->buildTargetPath(
          $imageView->getUuid(),
          $imageView->getFileName(),
          null,
          null,
          false,
        );

        if ($this->shareRepository->findByTargetPath($targetPath) !== null) {
          continue;
        }

        $shareable = ShareableReference::forImage(ID::fromString($imageView->getUuid()));
        $context = $this->shareService->buildContext($shareable);

        $share = Share::create(ID::generate(), $shareable, $targetPath, DateTime::now(), $context);
        $this->shareStore->store($share);

        if (++$count % self::BATCH_SIZE === 0) {
          $this->entityManager->flush();
          $this->entityManager->clear();
        }
      } catch (\Throwable $e) {
        $errors[] = sprintf('image %s: %s', $imageView->getFileName(), $e->getMessage());
      }
    }

    $this->entityManager->flush();

    if ($errors !== []) {
      throw new \RuntimeException('Failed to seed some share records: ' . implode('; ', $errors));
    }
  }

  public function down(): void {}

  public function getDescription(): string {
    return 'Seed share records for existing images';
  }
}
