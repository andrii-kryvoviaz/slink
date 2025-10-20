<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image;

use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Slink\Image\Domain\Service\ImageUrlSignatureInterface;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Application\Query\QueryTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use UI\Http\Rest\Response\ContentResponse;

#[AsController]
#[Route(path: '/image/{id}.{ext}', name: 'get_image', methods: ['GET'])]
final readonly class GetImageController {
  use CommandTrait;
  use QueryTrait;

  public function __construct(
    private ImageUrlSignatureInterface $signatureService
  ) {
  }
  
  public function __invoke(
    Request $request,
    #[MapQueryString] GetImageContentQuery $query,
    string $id,
    string $ext
  ): ContentResponse {
    $validatedQuery = $this->validateAndSanitizeQuery($request, $query, $id);
    
    $imageData = $this->ask($validatedQuery->withContext([
      'fileName' => "{$id}.{$ext}",
    ]));
    
    $this->handle(new AddImageViewCountCommand($id));
    
    return ContentResponse::file($imageData)->setCache(
      [
        'public' => true,
        'immutable' => true,
        'max_age' => 31536000,
      ]
    );
  }

  private function validateAndSanitizeQuery(
    Request $request,
    GetImageContentQuery $query,
    string $imageId
  ): GetImageContentQuery {
    $width = $query->getWidth();
    $height = $query->getHeight();
    $crop = $query->isCropped();
    $signature = $request->query->get('s');

    if ($width === null && $height === null && !$crop) {
      return $query;
    }

    $params = array_filter([
      'width' => $width,
      'height' => $height,
      'crop' => $crop,
    ], fn($value) => $value !== null && $value !== false);

    if (!is_string($signature) || !$this->signatureService->verify(
      $imageId,
      $params,
      $signature
    )) {
      return new GetImageContentQuery();
    }

    return $query;
  }
}
