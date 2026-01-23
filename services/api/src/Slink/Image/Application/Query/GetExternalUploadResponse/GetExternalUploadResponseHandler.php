<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetExternalUploadResponse;

use Slink\Share\Application\Command\CreateShare\CreateShareCommand;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareParams;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class GetExternalUploadResponseHandler implements QueryHandlerInterface {
  public function __construct(
    private CommandBusInterface $commandBus,
    private ShareUrlBuilderInterface $shareUrlBuilder,
    private ShareServiceInterface $shareService,
  ) {}

  /**
   * @return array<string, string>
   */
  public function __invoke(GetExternalUploadResponseQuery $query): array {
    $imageId = $query->getImageId();
    $fileName = $query->getFileName();

    $url = $this->createShareAndResolveUrl($imageId, $fileName);
    $thumbnailUrl = $this->createShareAndResolveUrl($imageId, $fileName, 300, 300, true);

    return [
      'url' => $url,
      'thumbnailUrl' => $thumbnailUrl,
      'id' => $imageId
    ];
  }

  private function createShareAndResolveUrl(
    string $imageId,
    string $fileName,
    ?int $width = null,
    ?int $height = null,
    bool $crop = false
  ): string {
    $targetPath = $this->shareUrlBuilder->buildTargetUrl($imageId, $fileName, $width, $height, $crop);
    $shareable = ShareableReference::forImage(ID::fromString($imageId));
    $params = ShareParams::withTargetPath($shareable, $targetPath);

    $result = $this->commandBus->handleSync(new CreateShareCommand($params));

    return $this->shareService->resolveUrl($result->getShare(), isAbsolute:false);
  }
}
