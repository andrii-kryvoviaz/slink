<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Storage;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\Storage\Application\Command\ClearCache\ClearCacheCommand;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/storage/cache', methods: ['DELETE'])]
#[IsGranted(UserRole::Admin->value)]
final readonly class ClearCacheController {
  use CommandTrait;

  public function __invoke(): JsonResponse {
    $command = new ClearCacheCommand();
    $clearedCount = $this->handleSync($command);

    return ApiResponse::fromPayload([
      'cleared' => $clearedCount,
      'message' => sprintf('Successfully cleared %d cached file(s)', $clearedCount)
    ]);
  }
}
