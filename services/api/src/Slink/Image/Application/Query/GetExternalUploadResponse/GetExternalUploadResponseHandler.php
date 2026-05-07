<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetExternalUploadResponse;

use Slink\Share\Application\Command\CreateShare\CreateShareCommand;
use Slink\Share\Application\Command\PublishShare\PublishShareCommand;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareParams;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\UserPreferencesRepositoryInterface;

final readonly class GetExternalUploadResponseHandler implements QueryHandlerInterface {
  public function __construct(
    private CommandBusInterface $commandBus,
    private ShareUrlBuilderInterface $shareUrlBuilder,
    private ShareServiceInterface $shareService,
    private UserPreferencesRepositoryInterface $preferencesRepository,
  ) {}

  /**
   * @return array<string, string>
   */
  public function __invoke(GetExternalUploadResponseQuery $query): array {
    $imageId = $query->getImageId();
    $fileName = $query->getFileName();
    $autoPublish = $this->shouldAutoPublish($query->getUserId());

    $url = $this->createShareAndResolveUrl($imageId, $fileName, autoPublish: $autoPublish);
    $thumbnailUrl = $this->createShareAndResolveUrl($imageId, $fileName, 300, 300, true, $autoPublish);

    return [
      'url' => $url,
      'thumbnailUrl' => $thumbnailUrl,
      'id' => $imageId
    ];
  }

  private function shouldAutoPublish(string $userId): bool {
    $view = $this->preferencesRepository->findByUserId($userId);

    if ($view === null) {
      return false;
    }

    return $view->getPreferences()->getExternalUploadAutoPublish() === true;
  }

  private function createShareAndResolveUrl(
    string $imageId,
    string $fileName,
    ?int $width = null,
    ?int $height = null,
    bool $crop = false,
    bool $autoPublish = false,
  ): string {
    $targetPath = $this->shareUrlBuilder->buildTargetPath($imageId, $fileName, $width, $height, $crop);
    $shareable = ShareableReference::forImage(ID::fromString($imageId));
    $params = ShareParams::withTargetPath($shareable, $targetPath);

    $result = $this->commandBus->handleSync(new CreateShareCommand($params));
    $share = $result->getShare();

    if ($autoPublish && !$share->isPublished()) {
      $this->commandBus->handleSync(new PublishShareCommand($share->aggregateRootId()->toString()));
    }

    return $this->shareService->resolveUrl($share, isAbsolute:false);
  }
}
