<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Collection;

use Slink\Collection\Application\Query\GetCollectionCover\GetCollectionCoverQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[Route(path: '/collection/{id}/cover', name: 'get_collection_cover', methods: ['GET'], priority: 1)]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class GetCollectionCoverController {
  use QueryTrait;

  public function __invoke(
    string $id,
    #[CurrentUser] JwtUser $user,
  ): Response {
    $query = new GetCollectionCoverQuery($id);

    $content = $this->ask($query->withContext([
      'userId' => $user->getIdentifier(),
    ]));

    return new Response($content, Response::HTTP_OK, [
      'Content-Type' => 'image/avif',
      'Cache-Control' => 'private, max-age=31536000',
    ]);
  }
}
