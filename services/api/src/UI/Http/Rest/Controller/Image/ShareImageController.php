<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/image/{id}/share', name: 'share_image', methods: ['GET'])]
final readonly class ShareImageController {
  public function __construct(
    private ImageRepositoryInterface $imageRepository,
    private ShareUrlBuilderInterface $shareUrlBuilder,
    private ShareServiceInterface $shareService,
  ) {}

  public function __invoke(
    string $id,
    #[MapQueryString] ?ShareImageRequest $request = null
  ): JsonResponse {
    $request = $request ?? new ShareImageRequest();
    $image = $this->imageRepository->oneById($id);

    $targetUrl = $this->shareUrlBuilder->buildTargetUrl(
      $id,
      $image->getFileName(),
      $request->width,
      $request->height,
      $request->crop,
      $request->format
    );

    $result = $this->shareService->share($id, $targetUrl);

    return new JsonResponse($result->toPayload());
  }
}
