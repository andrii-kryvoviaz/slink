<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Domain\Enum\License;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/licenses', name: 'get_licenses', methods: ['GET'])]
final readonly class GetLicensesController {
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
  ) {
  }

  public function __invoke(): ApiResponse {
    if (!$this->configurationProvider->get('image.enableLicensing')) {
      return ApiResponse::fromPayload(['licenses' => []]);
    }

    return ApiResponse::fromPayload(['licenses' => License::allToArray()]);
  }
}
