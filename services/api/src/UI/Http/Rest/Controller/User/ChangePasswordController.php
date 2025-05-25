<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\ChangePassword\ChangePasswordCommand;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/user/change-password', name: 'user_change_password', methods: ['POST'])]
final class ChangePasswordController {
  use CommandTrait;
  
  public function __invoke(
    #[CurrentUser] JwtUser $user,
    #[MapRequestPayload] ChangePasswordCommand $command
  ): ApiResponse {
    $this->handle($command->withContext([
      'userId' => $user->getUserIdentifier(),
    ]));
    
    return ApiResponse::empty();
  }
}