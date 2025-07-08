<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\CreateApiKey\CreateApiKeyCommand;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/user/api-keys', name: 'create_api_key', methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class CreateApiKeyController {
  use CommandTrait;

  public function __invoke(
    #[MapRequestPayload] CreateApiKeyCommand $command,
    #[CurrentUser] JwtUser $user
  ): ApiResponse {
    $key = $this->handleSync($command->withContext([
      'userId' => $user->getIdentifier()
    ]));

    return ApiResponse::fromPayload([
      'key' => $key,
      'keyId' => $command->getId()->toString(),
      'name' => $command->getName()
    ]);
  }
}
