<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slik\Shared\Application\Query\QueryTrait;
use Slik\User\Application\Query\User\FindByEmail\FindByEmailQuery;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/user', name: 'find_user', methods: ['GET'])]
final readonly class GetUserByEmailController {
  use QueryTrait;

  public function __invoke(
    #[MapQueryString] FindByEmailQuery $query,
  ): ApiResponse {
    $user = $this->ask($query);

    return ApiResponse::one($user);
  }
}