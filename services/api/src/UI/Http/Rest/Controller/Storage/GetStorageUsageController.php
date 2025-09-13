<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Storage;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\Storage\Application\Query\GetStorageUsage\GetStorageUsageQuery;
use Slink\Storage\Domain\Exception\StorageUsageMetricsDisabledException;
use Slink\User\Domain\Enum\UserRole;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/storage/usage', methods: ['GET'])]
#[IsGranted(UserRole::Admin->value)]
final readonly class GetStorageUsageController {
  use QueryTrait;
  
  public function __invoke(): JsonResponse {
    try {
      $query = new GetStorageUsageQuery();
      $response = $this->ask($query);
      
      return ApiResponse::fromPayload($response);
    } catch (StorageUsageMetricsDisabledException $e) {
      return new JsonResponse(null, 204);
    }
  }
}