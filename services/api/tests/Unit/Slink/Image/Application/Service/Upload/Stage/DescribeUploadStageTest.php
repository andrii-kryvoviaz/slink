<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload\Stage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\Upload\Stage\DescribeUploadStage;
use Slink\Image\Domain\Exception\UndeterminableImageExtensionException;
use Slink\Image\Domain\Factory\ImageMetadataFactory;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Unit\Slink\Image\Application\Service\Upload\UploadContextFactoryTrait;

class DescribeUploadStageTest extends TestCase {
  use UploadContextFactoryTrait;

  /**
   * @throws Exception
   */
  #[Test]
  public function itDerivesFileNameFromIdAndExtensionAndBuildsMetadata(): void {
    $metadataFactory = $this->createMock(ImageMetadataFactory::class);

    $stage = new DescribeUploadStage($metadataFactory);

    $file = $this->createStub(File::class);
    $file->method('guessExtension')->willReturn('webp');
    $file->method('getMimeType')->willReturn('image/webp');
    $file->method('getPathname')->willReturn('/tmp/test.webp');
    $file->method('getSize')->willReturn(512);

    $metadata = new ImageMetadata(512, 'image/webp', 800, 600, 'hash');
    $metadataFactory->expects($this->once())
      ->method('createFromFile')
      ->with($file)
      ->willReturn($metadata);

    $id = ID::generate();
    $context = $this->uploadContext($file, id: $id);

    $result = $stage->process($context);

    $this->assertSame(sprintf('%s.webp', $id->toString()), $result->fileName());
    $this->assertSame($metadata, $result->metadata());
    $this->assertSame('/tmp/test.webp', $result->mediaFile()->getPathname());
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itThrowsWhenExtensionCannotBeDetermined(): void {
    $metadataFactory = $this->createMock(ImageMetadataFactory::class);

    $stage = new DescribeUploadStage($metadataFactory);

    $file = $this->createStub(File::class);
    $file->method('guessExtension')->willReturn(null);

    $metadataFactory->expects($this->never())->method('createFromFile');

    $context = $this->uploadContext($file);

    $this->expectException(UndeterminableImageExtensionException::class);

    $stage->process($context);
  }
}
