<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Settings;

use Slink\Settings\Application\Query\RetrievePublicApplicationSettings\RetrievePublicApplicationSettingsQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/settings/public', methods: ['GET'])]
final readonly class RetrievePublicApplicationSettingsController {
  use QueryTrait;
  
  public function __invoke(
    #[MapQueryString] RetrievePublicApplicationSettingsQuery $query
  ): ApiResponse {
    $response = $this->ask($query->withContext([
      'groups' => ['public']
    ]));
    
    return ApiResponse::one($response);
  }
}
