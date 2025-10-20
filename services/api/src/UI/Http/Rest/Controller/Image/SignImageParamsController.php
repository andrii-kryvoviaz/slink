<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\SignImageParams\SignImageParamsCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/image/{id}/sign', name: 'sign_image_params', methods: ['GET'])]
final readonly class SignImageParamsController {
  use CommandTrait;

  public function __invoke(
    string $id,
    #[MapQueryString] ?SignImageParamsRequest $request = null
  ): JsonResponse {
    $request = $request ?? new SignImageParamsRequest();

    $signedParams = $this->handleSync(
      new SignImageParamsCommand(
        $id,
        $request->width,
        $request->height,
        $request->crop
      )
    );

    return new JsonResponse($signedParams->toPayload());
  }
}
