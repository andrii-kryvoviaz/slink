<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageById;

use Doctrine\ORM\NonUniqueResultException;
use Ramsey\Uuid\Uuid;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class GetImageByIdHandler implements QueryHandlerInterface {
  
  public function __construct(
    private ImageRepositoryInterface $repository,
    private ImageAnalyzerInterface $imageAnalyzer,
    private StorageInterface $storage,
    private ImageProcessorInterface $imageProcessor
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
    
    return Item::fromPayload(ImageView::class, [
      ...$imageView->toPayload(),
      'supportsResize' => $this->imageAnalyzer->supportsResize($mimeType),
      'supportsFormatConversion' => $this->imageAnalyzer->supportsFormatConversion($mimeType),
      'isAnimated' => $isAnimated,
      'url' => "/image/{$imageView->getFileName()}",
    ]);
  }

  private function checkIsAnimated(string $fileName, string $mimeType): bool {
    if (!$this->imageAnalyzer->supportsAnimation($mimeType)) {
      return false;
    }

    $imageOptions = ImageOptions::fromPayload([
      'fileName' => $fileName,
      'mimeType' => $mimeType,
    ]);

    $content = $this->storage->getImage($imageOptions);
    if (!$content) {
      return false;
    }

    $animationInfo = $this->imageProcessor->getAnimatedImageInfo($content);
    return $animationInfo->isAnimated();
  }
}