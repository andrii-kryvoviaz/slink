<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Admin\OAuth;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\MoveOAuthProvider\MoveOAuthProviderCommand;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/admin/oauth/providers/sort-order', name: 'admin_move_oauth_provider', methods: ['PATCH'])]
#[IsGranted(UserRole::Admin->value)]
final class MoveOAuthProviderController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] MoveOAuthProviderCommand $command,
  ): ApiResponse {
    $this->handle($command);

    return ApiResponse::empty();
  }
}
