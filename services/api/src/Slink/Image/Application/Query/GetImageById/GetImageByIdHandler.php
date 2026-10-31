<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageById;

use Doctrine\ORM\NonUniqueResultException;
use Ramsey\Uuid\Uuid;
use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\Exception\ForbiddenException;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Domain\FileSystem\Storage\StorageInterface;
use Slink\User\Infrastructure\Auth\JwtUser;

final readonly class GetImageByIdHandler implements QueryHandlerInterface {
  
  public function __construct(
    private ImageRepositoryInterface $repository,
    private ImageAnalyzerInterface $imageAnalyzer,
    private StorageInterface $storage,
    private CollectionItemRepositoryInterface $collectionItemRepository,
    private BookmarkRepositoryInterface $bookmarkRepository,
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
      throw new ForbiddenException();
    }

    $mimeType = $imageView->getMimeType();
    $isAnimated = $this->checkIsAnimated($imageView->getFileName(), $mimeType);
    $supportsResize = $this->imageAnalyzer->supportsResize($mimeType);

    $imageId = (string) $imageView->getUuid();
    $fileName = $imageView->getFileName();
    $collectionsByImageId = $this->collectionItemRepository->getCollectionsByImageIds([$imageId]);
    $collections = $collectionsByImageId[$imageId] ?? [];

    return Item::fromPayload(ImageView::class, [
      ...$imageView->toPayload(),
      'bookmarkCount' => $this->bookmarkRepository->countByImageId($imageId),
      'supportsResize' => $supportsResize,
      'supportsFormatConversion' => $this->imageAnalyzer->supportsFormatConversion($mimeType),
      'isAnimated' => $isAnimated,
      'src' => "/image/{$fileName}",
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

    return $this->imageAnalyzer->isAnimated($content);
  }
}