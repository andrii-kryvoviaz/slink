<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\UploadImage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Image\Application\Command\UploadImage\UploadImageHandler;
use Slink\Image\Application\Command\UploadImage\UploadImageResult;
use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPipeline;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\HttpFoundation\File\File;

class UploadImageHandlerTest extends TestCase {

  /**
   * @throws Exception
   */
  #[Test]
  public function itRunsPipelineWithContextBuiltFromCommand(): void {
    $pipeline = $this->createMock(UploadPipeline::class);

    $file = $this->createStub(File::class);
    $imageId = ID::generate();
    $userId = ID::generate();

    $command = $this->createStub(UploadImageCommand::class);
    $command->method('getImageFile')->willReturn($file);
    $command->method('getId')->willReturn($imageId);
    $command->method('isPublic')->willReturn(true);
    $command->method('getDescription')->willReturn('A description');
    $command->method('getTagIds')->willReturn([]);
    $command->method('getCollectionIds')->willReturn([]);

    $resolvedContext = $this->createStub(UploadContext::class);
    $resolvedContext->method('fileName')->willReturn('resolved.webp');

    $pipeline->expects($this->once())
      ->method('run')
      ->with($this->callback(static function (UploadContext $context) use ($imageId, $userId, $file): bool {
        return $context->id() === $imageId
          && $context->userId() !== null
          && $context->userId()->toString() === $userId->toString()
          && $context->requestedPublic() === true
          && $context->description() === 'A description'
          && $context->file() === $file;
      }))
      ->willReturn($resolvedContext);

    $handler = new UploadImageHandler($pipeline);

    $result = $handler($command, $userId->toString());

    $this->assertInstanceOf(UploadImageResult::class, $result);
    $this->assertSame('resolved.webp', $result->getFileName());
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itBuildsContextWithNullUserForGuestUpload(): void {
    $pipeline = $this->createMock(UploadPipeline::class);

    $command = $this->createStub(UploadImageCommand::class);
    $command->method('getImageFile')->willReturn($this->createStub(File::class));
    $command->method('getId')->willReturn(ID::generate());
    $command->method('isPublic')->willReturn(false);
    $command->method('getDescription')->willReturn('');
    $command->method('getTagIds')->willReturn([]);
    $command->method('getCollectionIds')->willReturn([]);

    $resolvedContext = $this->createStub(UploadContext::class);
    $resolvedContext->method('fileName')->willReturn('guest.webp');

    $pipeline->expects($this->once())
      ->method('run')
      ->with($this->callback(static fn(UploadContext $context): bool => $context->userId() === null))
      ->willReturn($resolvedContext);

    $handler = new UploadImageHandler($pipeline);

    $result = $handler($command);

    $this->assertSame('guest.webp', $result->getFileName());
  }
}
