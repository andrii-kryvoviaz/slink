<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Share;

use Slink\Share\Application\Command\SetSharePassword\SetSharePasswordCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/share/{id}/password', name: 'set_share_password', methods: ['PUT'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class SetSharePasswordController {
  use CommandTrait;

  public function __invoke(
    string $id,
    #[MapRequestPayload] SetSharePasswordCommand $command,
  ): ApiResponse {
    $this->handle($command->withContext(['shareId' => $id]));

    return ApiResponse::empty();
  }
}
