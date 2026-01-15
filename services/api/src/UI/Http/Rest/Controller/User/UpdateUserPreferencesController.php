<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\UpdateUserPreferences\UpdateUserPreferencesCommand;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/user/preferences', name: 'update_user_preferences', methods: ['PATCH'])]
final class UpdateUserPreferencesController {
  use CommandTrait;

  public function __invoke(
    #[CurrentUser] JwtUser $user,
    #[MapRequestPayload] UpdateUserPreferencesCommand $command
  ): ApiResponse {
    $this->handle($command->withContext([
      'userId' => $user->getUserIdentifier(),
    ]));

    return ApiResponse::empty();
  }
}
