<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Application\Query\User\GetUserList\GetUserListQuery;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route('/users/{page}', name: 'get_user_list', requirements: ['page' => '\d+'], methods: ['GET'])]
#[IsGranted('ROLE_ADMIN')]
final class GetUserListController {
  use QueryTrait;
  
  public function __invoke(
    #[CurrentUser] JwtUser $user,
    #[MapQueryString] GetUserListQuery $query,
    int $page
  ): ApiResponse {
    $response = $this->ask($query->withContext([
      'page' => $page,
      'groups' => ['internal', 'public']
    ]));
    
    return ApiResponse::collection($response);
  }
}