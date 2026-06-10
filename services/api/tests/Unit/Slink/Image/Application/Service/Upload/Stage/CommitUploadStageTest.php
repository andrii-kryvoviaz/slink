<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload\Stage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\CollectionMembershipAssigner;
use Slink\Image\Application\Service\ImageTagAssigner;
use Slink\Image\Application\Service\Upload\Stage\CommitUploadStage;
use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Domain\Context\ImageCreationContext;
use Slink\Image\Domain\Exception\DuplicateImageException;
use Slink\Image\Domain\Exception\UnauthorizedTagAccessException;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\Specification\ImageDuplicateSpecificationInterface;
use Slink\Image\Domain\ValueObject\ImageFile;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Unit\Slink\Image\Application\Service\Upload\UploadContextFactoryTrait;

class CommitUploadStageTest extends TestCase {
  use UploadContextFactoryTrait;

  /**
   * @throws Exception
   * @throws DateTimeException
   */
  private function describedContext(?ID $userId = null): UploadContext {
    $file = $this->createStub(File::class);
    $file->method('getPathname')->willReturn('/tmp/test.webp');

    return $this
      ->uploadContext($file, userId: $userId ?? ID::generate(), description: 'desc')
      ->withDescribed(
        '123.webp',
        new ImageFile('/tmp/test.webp', 'image/webp', 'webp', 512),
        new ImageMetadata(512, 'image/webp', 800, 600, 'hash'),
      )
      ->withVisibility(true);
  }

  /**
   * @throws Exception
   * @throws DateTimeException
   */
  #[Test]
  public function itExecutesSideEffectsInSafeOrder(): void {
    $calls = [];

    $duplicateSpec = $this->createStub(ImageDuplicateSpecificationInterface::class);
    $creationContext = new ImageCreationContext($duplicateSpec);

    $tagAssigner = $this->createMock(ImageTagAssigner::class);
    $tagAssigner->expects($this->once())->method('assign')->willReturnCallback(static function () use (&$calls): void {
      $calls[] = 'tags';
    });

    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->once())->method('upload')->willReturnCallback(static function () use (&$calls): void {
      $calls[] = 'storage';
    });

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->expects($this->once())->method('store')->willReturnCallback(static function () use (&$calls): void {
      $calls[] = 'store';
    });

    $collectionAssigner = $this->createMock(CollectionMembershipAssigner::class);
    $collectionAssigner->expects($this->once())->method('assign')->willReturnCallback(static function () use (&$calls): void {
      $calls[] = 'collections';
    });

    $stage = new CommitUploadStage($creationContext, $tagAssigner, $storage, $imageRepository, $collectionAssigner);

    $stage->process($this->describedContext());

    $this->assertSame(['tags', 'storage', 'store', 'collections'], $calls);
  }

  /**
   * @throws Exception
   * @throws DateTimeException
   */
  #[Test]
  public function itAbortsBeforeStorageWhenTagAuthorizationFails(): void {
    $duplicateSpec = $this->createStub(ImageDuplicateSpecificationInterface::class);
    $creationContext = new ImageCreationContext($duplicateSpec);

    $userId = ID::generate();
    $tagAssigner = $this->createMock(ImageTagAssigner::class);
    $tagAssigner->expects($this->once())->method('assign')->willThrowException(
      new UnauthorizedTagAccessException(ID::generate(), $userId),
    );

    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->never())->method('upload');

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->expects($this->never())->method('store');

    $collectionAssigner = $this->createMock(CollectionMembershipAssigner::class);
    $collectionAssigner->expects($this->never())->method('assign');

    $stage = new CommitUploadStage($creationContext, $tagAssigner, $storage, $imageRepository, $collectionAssigner);

    $this->expectException(UnauthorizedTagAccessException::class);

    $stage->process($this->describedContext($userId));
  }

  /**
   * @throws Exception
   * @throws DateTimeException
   */
  #[Test]
  public function itAbortsBeforeStoreWhenStorageFails(): void {
    $duplicateSpec = $this->createStub(ImageDuplicateSpecificationInterface::class);
    $creationContext = new ImageCreationContext($duplicateSpec);

    $tagAssigner = $this->createStub(ImageTagAssigner::class);

    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->once())->method('upload')->willThrowException(new \RuntimeException('disk full'));

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->expects($this->never())->method('store');

    $collectionAssigner = $this->createMock(CollectionMembershipAssigner::class);
    $collectionAssigner->expects($this->never())->method('assign');

    $stage = new CommitUploadStage($creationContext, $tagAssigner, $storage, $imageRepository, $collectionAssigner);

    $this->expectException(\RuntimeException::class);

    $stage->process($this->describedContext());
  }

  /**
   * @throws Exception
   * @throws DateTimeException
   */
  #[Test]
  public function itAbortsBeforeAnySideEffectWhenDuplicateDetected(): void {
    $existing = $this->createStub(ImageView::class);

    $duplicateSpec = $this->createMock(ImageDuplicateSpecificationInterface::class);
    $duplicateSpec->expects($this->once())->method('ensureNoDuplicate')->willThrowException(new DuplicateImageException($existing));

    $creationContext = new ImageCreationContext($duplicateSpec);

    $tagAssigner = $this->createMock(ImageTagAssigner::class);
    $tagAssigner->expects($this->never())->method('assign');

    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->never())->method('upload');

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->expects($this->never())->method('store');

    $collectionAssigner = $this->createMock(CollectionMembershipAssigner::class);
    $collectionAssigner->expects($this->never())->method('assign');

    $stage = new CommitUploadStage($creationContext, $tagAssigner, $storage, $imageRepository, $collectionAssigner);

    $this->expectException(DuplicateImageException::class);

    $stage->process($this->describedContext());
  }
}
