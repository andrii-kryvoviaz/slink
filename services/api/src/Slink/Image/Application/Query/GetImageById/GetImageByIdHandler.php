<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageById;

use Doctrine\ORM\NonUniqueResultException;
use Ramsey\Uuid\Uuid;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class GetImageByIdHandler implements QueryHandlerInterface {
  
  public function __construct(
    private ImageRepositoryInterface $repository,
    private ImageAnalyzerInterface $imageAnalyzer,
    private StorageInterface $storage,
    private ImageProcessorInterface $imageProcessor,
    private CollectionItemRepositoryInterface $collectionItemRepository,
  ) {
  }
  
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function __invoke(GetImageByIdQuery $query, ?JwtUser $user): Item {
    if(!Uuid::isValid($query->getId())) {
      throw new NotFoundException();
    }
    
    $imageView = $this->repository->oneById($query->getId());
    
    if($imageView->getUser()?->getUuid() !== $user?->getIdentifier()) {
      throw new AccessDeniedException();
    }

    $mimeType = $imageView->getMimeType();
    $isAnimated = $this->checkIsAnimated($imageView->getFileName(), $mimeType);
    
    $imageId = (string) $imageView->getUuid();
    $collectionsByImageId = $this->collectionItemRepository->getCollectionsByImageIds([$imageId]);
    $collections = $collectionsByImageId[$imageId] ?? [];

    return Item::fromPayload(ImageView::class, [
      ...$imageView->toPayload(),
      'supportsResize' => $this->imageAnalyzer->supportsResize($mimeType),
      'supportsFormatConversion' => $this->imageAnalyzer->supportsFormatConversion($mimeType),
      'isAnimated' => $isAnimated,
      'url' => "/image/{$imageView->getFileName()}",
      'collections' => array_map(fn(CollectionView $c) => ['id' => (string) $c->getUuid(), 'name' => $c->getName()], $collections),
    ]);
  }

  private function checkIsAnimated(string $fileName, string $mimeType): bool {
    if (!$this->imageAnalyzer->supportsAnimation($mimeType)) {
      return false;
    }

    $content = $this->storage->readImage($fileName);
    if (!$content) {
      return false;
    }

    $animationInfo = $this->imageProcessor->getAnimatedImageInfo($content);
    return $animationInfo->isAnimated();
  }
}