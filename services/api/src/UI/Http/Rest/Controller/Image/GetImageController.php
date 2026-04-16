<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use UI\Http\Rest\Response\ContentResponse;

#[AsController]
#[Route(path: '/image/{id}.{ext}', name: 'get_image', methods: ['GET'])]
final readonly class GetImageController {
  use CommandTrait;
  use QueryTrait;

  public function __invoke(
    #[MapQueryString] GetImageContentQuery $query,
    string $id,
    string $ext,
    #[CurrentUser] ?JwtUser $user = null
  ): ContentResponse {
    $imageData = $this->ask($query->withFormat($ext)->withContext([
      'fileName' => "{$id}.{$ext}",
      'requestedFormat' => $ext,
    ]));

    $this->handle((new AddImageViewCountCommand($id))->withContext([
      'userId' => $user?->getIdentifier(),
    ]));

    return ContentResponse::file($imageData)->setCache([
      'public' => $user === null,
      'immutable' => true,
      'max_age' => 31536000,
    ]);
  }
}
