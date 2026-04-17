<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Share;

use Slink\Share\Application\Command\SetShareExpiration\SetShareExpirationCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/share/{id}/expiration', name: 'set_share_expiration', methods: ['PUT'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class SetShareExpirationController {
  use CommandTrait;

  public function __invoke(
    string $id,
    #[MapRequestPayload] SetShareExpirationCommand $command,
  ): ApiResponse {
    $this->handle($command->withContext(['shareId' => $id]));

    return ApiResponse::empty();
  }
}
