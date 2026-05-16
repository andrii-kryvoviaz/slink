<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageContent;

use Slink\Image\Application\Service\ImageContentLoader;
use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAccessContext;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Slink\Shared\Application\Http\CachePolicy;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Application\Security\Viewer;
use Slink\Shared\Domain\Service\UrlSignatureInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class GetImageContentHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageRepositoryInterface $repository,
    private AuthorizationCheckerInterface $access,
    private ShareUrlBuilderInterface $shareUrlBuilder,
    private ImageContentLoader $loader,
    private UrlSignatureInterface $signature,
  ) {
  }

  /**
   * @throws NotFoundException
   */
  public function __invoke(GetImageContentQuery $query): Item {
    $imageId = (string) $query->getImageId();
    $imageView = $this->repository->oneById($imageId);

    $targetPath = $this->shareUrlBuilder->buildTargetPath(
      $imageId,
      $imageView->getFileName(),
      $query->getWidth(),
      $query->getHeight(),
      $query->isCropped(),
      $query->getFormat(),
      $query->getFilter(),
    );

    $context = new ImageAccessContext($imageView, $targetPath);

    if (!$this->access->isGranted(ImageAccess::View, $context)) {
      throw new NotFoundException();
    }

    $transforms = $this->authorizedTransforms(
      $imageId,
      $query->getTransformParams(),
      $query->getTransformSignature(),
    );

    $payload = $this->loader->load(
      $imageView,
      $query->getFormat(),
      $transforms,
    );

    $cachePolicy = CachePolicy::forImageAccess(
      Viewer::fromIdentifier($query->getUserId())->owns($imageView),
    );

    return Item::fromContent($payload->content, $payload->mimeType, $cachePolicy);
  }

  /**
   * @param array<string, mixed> $transforms
   * @return array<string, mixed>
   */
  private function authorizedTransforms(string $imageId, array $transforms, ?string $signature): array {
    $signedPayload = array_filter(
      [
        'width' => $transforms['width'] ?? null,
        'height' => $transforms['height'] ?? null,
        'crop' => $transforms['crop'] ?? null,
        'filter' => $transforms['filter'] ?? null,
      ],
      static fn ($value): bool => $value !== null && $value !== false,
    );

    if ($signedPayload === []) {
      return $transforms;
    }

    if ($signature === null) {
      return [];
    }

    if (!$this->signature->verify($imageId, $signedPayload, $signature)) {
      return [];
    }

    return $transforms;
  }
}
