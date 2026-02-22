<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Admin\OAuth;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\DeleteOAuthProvider\DeleteOAuthProviderCommand;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/admin/oauth/providers/{id}', name: 'admin_delete_oauth_provider', methods: ['DELETE'])]
#[IsGranted(UserRole::Admin->value)]
final class DeleteOAuthProviderController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] DeleteOAuthProviderCommand $command,
    string $id,
  ): ApiResponse {
    $this->handle($command->withContext([
      'id' => $id,
    ]));

    return ApiResponse::empty();
  }
}
