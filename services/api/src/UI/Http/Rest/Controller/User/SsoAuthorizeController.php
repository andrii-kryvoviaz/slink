<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\SsoAuthorize\SsoAuthorizeCommand;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/auth/sso/authorize', name: 'auth_sso_authorize', methods: ['POST'])]
final class SsoAuthorizeController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] SsoAuthorizeCommand $command,
  ): ApiResponse {
    $authorizationUrl = $this->handleSync($command);

    return ApiResponse::fromPayload(['authorizationUrl' => $authorizationUrl]);
  }
}
