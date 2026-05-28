<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Share;

use Slink\Share\Application\Command\UnlockShare\UnlockShareCommand;
use Slink\Shared\Application\Command\CommandTrait;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/share/{id}/unlock', name: 'unlock_share', methods: ['POST'])]
final readonly class UnlockShareController {
  use CommandTrait;

  public function __invoke(
    string $id,
    #[MapRequestPayload] UnlockShareCommand $command,
  ): ApiResponse {
    /** @var ?Cookie $cookie */
    $cookie = $this->handleSync($command->withContext(['shareId' => $id]));

    if ($cookie === null) {
      return ApiResponse::empty();
    }

    return ApiResponse::empty(cookies: [$cookie]);
  }
}
