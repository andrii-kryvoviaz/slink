<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Tag;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\Tag\Application\Query\GetTagListExists\GetTagListExistsQuery;
use Slink\User\Domain\Contracts\UserInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/tags/exists', name: 'get_tag_list_exists', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetTagListExistsController {
  use QueryTrait;

  public function __invoke(
    #[CurrentUser] UserInterface              $user,
    #[MapQueryString] GetTagListExistsQuery   $query = new GetTagListExistsQuery(),
  ): ApiResponse {
    $exists = (bool) $this->ask($query->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return ApiResponse::fromPayload(['exists' => $exists]);
  }
}
