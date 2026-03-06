<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\SsoSignIn\SsoSignInCommand;
use Slink\User\Domain\Exception\UserPendingApprovalException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/auth/sso/token', name: 'auth_sso_token', methods: ['POST'])]
final class SsoTokenController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] SsoSignInCommand $command,
  ): ApiResponse {
    try {
      $tokenPair = $this->handleSync($command);
      return ApiResponse::fromSerializable($tokenPair);
    } catch (UserPendingApprovalException $e) {
      return ApiResponse::fromPayload([
        'approval_required' => true,
        ...$e->toPayload(),
      ]);
    }
  }
}
