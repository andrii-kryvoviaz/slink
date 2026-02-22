<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Admin\OAuth;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\CreateOAuthProvider\CreateOAuthProviderCommand;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/admin/oauth/providers', name: 'admin_create_oauth_provider', methods: ['POST'])]
#[IsGranted(UserRole::Admin->value)]
final class CreateOAuthProviderController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] CreateOAuthProviderCommand $command,
  ): ApiResponse {
    $id = $this->handleSync($command);

    return ApiResponse::created($id);
  }
}
