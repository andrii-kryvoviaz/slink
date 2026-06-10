<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UploadImage;

use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPipeline;
use Slink\Image\Domain\Exception\DuplicateImageException;
use Slink\Image\Domain\Exception\UnauthorizedTagAccessException;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\Date\DateTimeException;

final readonly class UploadImageHandler implements CommandHandlerInterface {
  public function __construct(
    private UploadPipeline $pipeline,
  ) {
  }

  /**
   * @throws DateTimeException
   * @throws DuplicateImageException
   * @throws UnauthorizedTagAccessException
   */
  public function __invoke(UploadImageCommand $command, ?string $userId = null): UploadImageResult {
    $context = $this->pipeline->run(UploadContext::fromCommand($command, $userId));

    return new UploadImageResult($context->fileName());
  }
}
