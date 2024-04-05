<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageContent;

use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\StorageInterface;

final readonly class GetImageContentHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageAnalyzerInterface $imageAnalyzer,
    private ImageRepositoryInterface $repository,
    private StorageInterface $storage,
  ) {
  }
  
  /**
   * @throws NotFoundException
   */
  public function __invoke(GetImageContentQuery $query, string $fileName): Item {
    $imageView = $this->repository->oneByFileName($fileName);
    
    $transformParams = $this->imageAnalyzer->supportsResize($imageView->getMimeType())
      ? $query->getTransformParams()
      : [];
    
    $imageOptions = ImageOptions::fromPayload([
      'fileName' => $imageView->getFileName(),
      'mimeType' => $imageView->getMimeType(),
      ...$transformParams
    ]);
    
    $imageContent = $this->storage->getImage($imageOptions);
    
    if($imageContent === null) {
      throw new NotFoundException();
    }
    
    return Item::fromContent($imageContent, $imageView->getMimeType());
  }
}