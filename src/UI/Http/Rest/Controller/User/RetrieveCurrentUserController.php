<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Query\User\FindUserById\FindUserByIdQuery;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/user', name: 'authenticated_user', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class RetrieveCurrentUserController {
  use QueryTrait;

  public function __invoke(
    #[CurrentUser] JwtUser $user
  ): ApiResponse {
    $user = $this->ask(new FindUserByIdQuery($user->getUserIdentifier()));

    return ApiResponse::one($user);
  }
}