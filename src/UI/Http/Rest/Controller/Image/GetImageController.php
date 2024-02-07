<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slik\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slik\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slik\Image\Domain\Image;
use Slik\Shared\Application\Command\CommandTrait;
use Slik\Shared\Application\Query\QueryTrait;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use UI\Http\Rest\Response\ContentResponse;

#[AsController]
#[Route(path: '/image/{id}.{ext}', name: 'get_image', methods: ['GET'])]
final readonly class GetImageController {
  use CommandTrait;
  use QueryTrait;
  
  public function __construct(private RequestStack $requestStack) {
  }
  /**
   * @throws NotFoundException
   */
  public function __invoke(Image $image, string $ext): ContentResponse {
    $request = $this->requestStack->getCurrentRequest();
    
    if(!$image->hasExtension($ext)) {
      throw new NotFoundException();
    }
    
    $this->handle(new AddImageViewCountCommand($image->aggregateRootId()->toString()));
    
    $imageData = $this->ask(new GetImageContentQuery(
      $image->getAttributes()->getFileName(),
      $image->getMetadata()->getMimeType(),
      (string) $request?->query->get('width', null),
      (string) $request?->query->get('height', null)
    ));
    
    return ContentResponse::file($imageData, $image->getMetadata()->getMimeType());
  }
}