<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Comment;

use Slink\Comment\Application\Query\GetCommentsByImage\GetCommentsByImageQuery;
use Slink\Shared\Application\Query\QueryTrait;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/image/{imageId}/comments', name: 'get_comments', methods: ['GET'])]
final class GetCommentsController {
  use QueryTrait;

  public function __invoke(
    string $imageId,
    int $page = 1,
    int $limit = 50,
  ): ApiResponse {
    $query = new GetCommentsByImageQuery($imageId, $page, $limit);
    $result = $this->ask($query);

    return ApiResponse::collection($result);
  }
}
