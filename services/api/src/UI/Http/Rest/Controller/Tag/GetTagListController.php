<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Tag;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\Tag\Application\Query\GetTagList\GetTagListQuery;
use Slink\User\Domain\Contracts\UserInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/tags', name: 'get_tag_list', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetTagListController {
  use QueryTrait;

  public function __invoke(
    #[MapQueryString] GetTagListQuery $query,
    #[CurrentUser] UserInterface      $user
  ): ApiResponse {
    $tags = $this->ask($query->withContext([
      'userId' => $user->getIdentifier()
    ]));

    return ApiResponse::collection($tags);
  }
}