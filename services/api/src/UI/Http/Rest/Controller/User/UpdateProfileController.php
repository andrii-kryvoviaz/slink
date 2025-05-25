<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\UpdateProfile\UpdateProfileCommand;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/user/profile', name: 'update_user_profile', methods: ['PATCH'])]
final class UpdateProfileController {
  use CommandTrait;
  
  public function __invoke(
    #[CurrentUser] JwtUser $user,
    #[MapRequestPayload] UpdateProfileCommand $command
  ): ApiResponse {
    $this->handle($command->withContext([
      'userId' => $user->getUserIdentifier(),
    ]));
    
    return ApiResponse::empty();
  }
}