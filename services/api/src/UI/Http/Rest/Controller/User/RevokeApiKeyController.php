<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\RevokeApiKey\RevokeApiKeyCommand;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/user/api-keys/{keyId}', name: 'revoke_api_key', methods: ['DELETE'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class RevokeApiKeyController {
  use CommandTrait;

  public function __invoke(
    string $keyId,
    #[CurrentUser] JwtUser $user
  ): ApiResponse {
    $command = new RevokeApiKeyCommand($keyId);
    
    $this->handle($command->withContext([
      'userId' => $user->getIdentifier()
    ]));

    return ApiResponse::empty();
  }
}
