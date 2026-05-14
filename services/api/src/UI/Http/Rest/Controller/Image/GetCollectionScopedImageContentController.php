<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slink\Image\Application\Query\GetCollectionScopedImageContent\GetCollectionScopedImageContentQuery;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use UI\Http\Rest\Response\ContentResponse;

#[AsController]
#[Route(
  path: '/image/collection/{collectionId}/items/{itemId}.{ext}',
  name: 'get_collection_scoped_image_content',
  methods: ['GET'],
)]
final readonly class GetCollectionScopedImageContentController {
  use CommandTrait;
  use QueryTrait;

  public function __invoke(
    string $collectionId,
    string $itemId,
    string $ext,
    #[CurrentUser] ?JwtUser $user = null,
  ): ContentResponse {
    $imageData = $this->ask(new GetCollectionScopedImageContentQuery($collectionId, $itemId));

    $this->handle((new AddImageViewCountCommand($itemId))->withContext([
      'userId' => $user?->getIdentifier(),
    ]));

    return ContentResponse::file($imageData);
  }
}
