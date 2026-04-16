<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\DataMigrations;

use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Share\Application\Command\CreateShare\CreateShareCommand;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareParams;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationInterface;

final class Migration20260416032039 implements DataMigrationInterface {
  public function __construct(
    private readonly ImageRepositoryInterface $imageRepository,
    private readonly ShareRepositoryInterface $shareRepository,
    private readonly CommandBusInterface $commandBus,
    private readonly ShareUrlBuilderInterface $shareUrlBuilder,
  ) {}

  public function up(): void {
    $filter = new ImageListFilter(limit: null);
    $errors = [];

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

        $params = ShareParams::withTargetPath(
          ShareableReference::forImage(ID::fromString($imageView->getUuid())),
          $targetPath,
        );

        $this->commandBus->handle(new CreateShareCommand($params));
      } catch (\Throwable $e) {
        $errors[] = sprintf('image %s: %s', $imageView->getFileName(), $e->getMessage());
      }
    }

    if ($errors !== []) {
      throw new \RuntimeException('Failed to seed some share records: ' . implode('; ', $errors));
    }
  }

  public function down(): void {}

  public function getDescription(): string {
    return 'Seed share records for existing images';
  }
}
