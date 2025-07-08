<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Query\GetApiKeys\GetApiKeysQuery;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/user/api-keys', name: 'get_api_keys', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetApiKeysController {
  use QueryTrait;

  public function __invoke(
    #[CurrentUser] JwtUser $user
  ): ApiResponse {
    $query = new GetApiKeysQuery(ID::fromString($user->getIdentifier()));
    $apiKeys = $this->ask($query);

    return ApiResponse::fromPayload(['data' => $apiKeys]);
  }
}
