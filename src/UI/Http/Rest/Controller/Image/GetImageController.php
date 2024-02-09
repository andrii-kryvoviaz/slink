<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slink\Image\Domain\Image;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
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