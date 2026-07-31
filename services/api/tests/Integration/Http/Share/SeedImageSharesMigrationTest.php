<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Share;

use PHPUnit\Framework\Attributes\Test;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Infrastructure\DataMigration\DataMigrationRunner;
use Slink\Shared\Infrastructure\Persistence\Doctrine\DataMigrations\Migration20260416032039;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Tests\Integration\Http\HttpTestCase;

final class SeedImageSharesMigrationTest extends HttpTestCase {
  #[Test]
  public function seedsShareForImageWithoutOneWhenNoSecurityTokenIsPresent(): void {
    $this->createUser('migration-owner@local.test', 'migrationowner', self::PASSWORD);
    $ownerToken = $this->login('migrationowner', self::PASSWORD);

    $imageId = $this->uploadImage($ownerToken, false);
    $targetPath = '/image/' . $this->imageFileName($imageId);

    self::assertSame(0, $this->countRowsByColumn('share', 'target_url', $targetPath));

    $this->clearSecurityToken();
    $this->runMigration();

    self::assertSame(1, $this->countRowsByColumn('share', 'target_url', $targetPath));
  }

  #[Test]
  public function doesNotDuplicateShareForImageThatAlreadyHasOne(): void {
    $this->createUser('migration-owner@local.test', 'migrationowner', self::PASSWORD);
    $ownerToken = $this->login('migrationowner', self::PASSWORD);

    $imageId = $this->uploadImage($ownerToken, false);
    $this->createImageShare($ownerToken, $imageId);
    $targetPath = '/image/' . $this->imageFileName($imageId);

    self::assertSame(1, $this->countRowsByColumn('share', 'target_url', $targetPath));

    $this->clearSecurityToken();
    $this->runMigration();

    self::assertSame(1, $this->countRowsByColumn('share', 'target_url', $targetPath));
  }

  private function imageFileName(string $imageId): string {
    /** @var ImageRepositoryInterface $imageRepository */
    $imageRepository = static::getContainer()->get(ImageRepositoryInterface::class);

    return $imageRepository->oneById($imageId)->getFileName();
  }

  private function clearSecurityToken(): void {
    /** @var TokenStorageInterface $tokenStorage */
    $tokenStorage = static::getContainer()->get('security.token_storage');
    $tokenStorage->setToken(null);
  }

  private function runMigration(): void {
    /** @var DataMigrationRunner $runner */
    $runner = static::getContainer()->get(DataMigrationRunner::class);
    $migration = $runner->findByVersion(Migration20260416032039::class);

    self::assertNotNull($migration, 'Migration20260416032039 is not registered as a data_migration service.');

    $migration->up();
  }
}
