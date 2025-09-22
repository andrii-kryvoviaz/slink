<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Tag;

use Slink\Shared\Application\Query\QueryTrait;
use Slink\Tag\Application\Query\GetTagById\GetTagByIdQuery;
use Slink\User\Domain\Contracts\UserInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/tags/{id}', name: 'get_tag_by_id', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final readonly class GetTagByIdController {
  use QueryTrait;

  public function __invoke(
    string $id,
    #[CurrentUser] UserInterface $user
  ): ApiResponse {
    $query = new GetTagByIdQuery($id);
    
    $tag = $this->ask($query->withContext([
      'userId' => $user->getIdentifier()
    ]));

    return ApiResponse::one($tag);
  }
}