<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload;

use PHPUnit\Framework\MockObject\Exception;
use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\ValueObject\UserPreferences;
use Symfony\Component\HttpFoundation\File\File;

trait UploadContextFactoryTrait {
  /**
   * @param array<string> $tagIds
   * @param array<string> $collectionIds
   * @throws Exception
   */
  private function uploadContext(
    File $file,
    ?ID $userId = null,
    bool $requestedPublic = false,
    string $description = '',
    array $tagIds = [],
    array $collectionIds = [],
    ?ID $id = null,
  ): UploadContext {
    $command = $this->createStub(UploadImageCommand::class);
    $command->method('getImageFile')->willReturn($file);
    $command->method('getId')->willReturn($id ?? ID::generate());
    $command->method('isPublic')->willReturn($requestedPublic);
    $command->method('getDescription')->willReturn($description);
    $command->method('getTagIds')->willReturn($tagIds);
    $command->method('getCollectionIds')->willReturn($collectionIds);

    return UploadContext::fromCommand($command, $userId?->toString());
  }

  /**
   * @throws Exception
   */
  private function contextWithFile(File $file): UploadContext {
    return $this->uploadContext($file);
  }

  /**
   * @throws Exception
   */
  private function contextWithPreferences(File $file, UserPreferences $preferences): UploadContext {
    return $this->uploadContext($file)->withPreferences($preferences);
  }
}
