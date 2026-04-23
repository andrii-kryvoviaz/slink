<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\DataMigrations;

use Doctrine\ORM\EntityManagerInterface;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationInterface;

final class Migration20260416155324 implements DataMigrationInterface {
  private const BATCH_SIZE = 100;

  public function __construct(
    private readonly ShareRepositoryInterface $shareRepository,
    private readonly ShareStoreRepositoryInterface $shareStore,
    private readonly EntityManagerInterface $entityManager,
  ) {}

  public function up(): void {
    $ids = array_map(
      static fn(ShareView $view): string => $view->getId(),
      iterator_to_array($this->shareRepository->findAllUnpublished(), false)
    );

    $errors = [];
    $count = 0;

    foreach ($ids as $id) {
      try {
        $share = $this->shareStore->get(ID::fromString($id));
        $share->publish();
        $this->shareStore->store($share);

        if (++$count % self::BATCH_SIZE === 0) {
          $this->entityManager->flush();
          $this->entityManager->clear();
        }
      } catch (\Throwable $e) {
        $errors[] = sprintf('share %s: %s', $id, $e->getMessage());
      }
    }

    $this->entityManager->flush();

    if ($errors !== []) {
      throw new \RuntimeException('Failed to publish some share records: ' . implode('; ', $errors));
    }
  }

  public function down(): void {}

  public function getDescription(): string {
    return 'Publish all existing share records';
  }
}
