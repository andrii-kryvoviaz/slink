<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slik\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slik\Image\Application\Query\GetImageById\GetImageByIdQuery;
use Slik\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slik\Shared\Application\Command\CommandTrait;
use Slik\Shared\Application\Query\QueryTrait;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ContentResponse;

#[AsController]
#[Route(path: '/image/{id}.{ext}', name: 'get_image', methods: ['GET'])]
final readonly class GetImageController {
  use CommandTrait;
  use QueryTrait;
  
  private Request $request;
  
  public function __construct(RequestStack $requestStack) {
    $this->request = $requestStack->getCurrentRequest();
  }
  /**
   * @throws NotFoundException
   */
  public function __invoke(string $id, string $ext): ContentResponse {
    $imageView = $this->ask(new GetImageByIdQuery($id, false));
    
    if($imageView->getAttributes()->getFileName() !== "$id.$ext") {
      throw new NotFoundException();
    }
    
    $this->handle(new AddImageViewCountCommand($id));
    
    $imageData = $this->ask(new GetImageContentQuery(
      $imageView->getAttributes()->getFileName(),
      $imageView->getMetadata()->getMimeType(),
      $this->request->query->get('width', null),
      $this->request->query->get('height', null)
    ));
    
    return ContentResponse::file($imageData, $imageView->getMetadata()->getMimeType());
  }
}