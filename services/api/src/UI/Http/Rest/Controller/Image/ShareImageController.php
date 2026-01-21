<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Share\Application\Command\CreateShare\CreateShareCommand;
use Slink\Share\Application\Command\CreateShare\CreateShareResult;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareParams;
use Slink\Share\Domain\ValueObject\ShareResponse;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/image/{id}/share', name: 'share_image', methods: ['GET'])]
final readonly class ShareImageController {
  use CommandTrait;

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

    $targetPath = $this->shareUrlBuilder->buildTargetUrl(
      $id,
      $image->getFileName(),
      $request->width,
      $request->height,
      $request->crop,
      $request->format
    );

    $shareable = ShareableReference::forImage(ID::fromString($id));
    $params = ShareParams::withTargetPath($shareable, $targetPath);
    $command = new CreateShareCommand($params);

    /** @var CreateShareResult $result */
    $result = $this->handleSync($command);

    $response = ShareResponse::fromShare(
      $result->getShare(),
      $this->shareService->resolveUrl($result->getShare()),
      $result->wasCreated(),
    );

    return new JsonResponse(
      $response->toPayload(),
      $result->wasCreated() ? Response::HTTP_CREATED : Response::HTTP_OK
    );
  }
}
