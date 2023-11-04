<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slik\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slik\Image\Application\Query\GetImageById\GetImageByIdQuery;
use Slik\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slik\Shared\Application\Command\CommandTrait;
use Slik\Shared\Application\Query\QueryTrait;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ContentResponse;

#[AsController]
#[Route(path: '/image/{id}.{ext}', name: 'get_image', methods: ['GET'])]
final readonly class GetImageController {
  use CommandTrait;
  use QueryTrait;
  
  /**
   * @throws NotFoundException
   */
  public function __invoke(string $id, string $ext): ContentResponse {
    $this->handle(new AddImageViewCountCommand($id));
    
    $imageView = $this->ask(new GetImageByIdQuery($id, false));
    
    if($imageView->getAttributes()->getFileName() !== "$id.$ext") {
      throw new NotFoundException();
    }
    
    $imageData = $this->ask(new GetImageContentQuery($imageView->getAttributes()->getFileName()));
    
    return ContentResponse::file($imageData, $imageView->getMetadata()->getMimeType());
  }
}