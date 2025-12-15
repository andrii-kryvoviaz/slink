<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Domain\Enum\License;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/licenses', name: 'get_licenses', methods: ['GET'])]
final readonly class GetLicensesController {
  public function __invoke(): ApiResponse {
    return ApiResponse::fromPayload(['licenses' => License::allToArray()]);
  }
}
