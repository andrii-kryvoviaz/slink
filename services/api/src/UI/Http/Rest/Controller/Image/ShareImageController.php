<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Share\Application\Command\ShareImage\ShareImageCommand;
use Slink\Share\Application\Query\FindShareByTargetUrl\FindShareByTargetUrlQuery;
use Slink\Share\Domain\Enum\ShareType;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/image/{id}/share', name: 'share_image', methods: ['GET'])]
final readonly class ShareImageController {
  use CommandTrait;
  use QueryTrait;

  public function __construct(
    private ImageRepositoryInterface $imageRepository,
    private ShareUrlBuilderInterface $shareUrlBuilder,
    private ConfigurationProviderInterface $configurationProvider,
  ) {
  }

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

    $shorteningEnabled = $this->configurationProvider->get('share.enableUrlShortening') ?? true;

    if (!$shorteningEnabled) {
      return new JsonResponse([
        'type' => ShareType::Signed->value,
        'targetUrl' => $targetUrl,
      ]);
    }

    /** @var ShareView|null $existingShare */
    $existingShare = $this->ask(new FindShareByTargetUrlQuery($targetUrl));

    if ($existingShare?->getShortUrl() !== null) {
      return new JsonResponse([
        'type' => ShareType::ShortUrl->value,
        'shortCode' => $existingShare->getShortUrl()->getShortCode(),
      ]);
    }

    /** @var Share $share */
    $share = $this->handleSync(new ShareImageCommand($id, $targetUrl, $shorteningEnabled));

    return new JsonResponse([
      'type' => ShareType::ShortUrl->value,
      'shortCode' => $share->getShortCode(),
    ]);
  }
}
