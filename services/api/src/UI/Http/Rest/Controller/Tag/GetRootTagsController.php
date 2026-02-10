<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Tag;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\Tag\Application\Query\GetRootTags\GetRootTagsQuery;
use Slink\User\Domain\Contracts\UserInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/tags/root', name: 'get_root_tags', methods: ['GET'], priority: 1)]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetRootTagsController {
  use QueryTrait;

  public function __invoke(
    #[CurrentUser] UserInterface $user
  ): ApiResponse {
    $query = new GetRootTagsQuery();

    $tags = $this->ask($query->withContext([
      'userId' => $user->getIdentifier()
    ]));

    return ApiResponse::collection($tags);
  }
}
