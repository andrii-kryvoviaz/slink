<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ContentResponse;

#[AsController]
#[Route(path: '/image/{id}.{ext}', name: 'get_image', methods: ['GET'])]
final readonly class GetImageController {
  use CommandTrait;
  use QueryTrait;
  
  public function __invoke(
    #[MapQueryString] GetImageContentQuery $query,
    string $id,
    string $ext
  ): ContentResponse {
    
    $imageData = $this->ask($query->withContext([
      'id' => $id,
      'ext' => $ext,
    ]));
    
    $this->handle(new AddImageViewCountCommand($id));
    
    return ContentResponse::file($imageData);
  }
}