<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageById;

use Doctrine\ORM\NonUniqueResultException;
use Ramsey\Uuid\Uuid;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

final readonly class GetImageByIdHandler implements QueryHandlerInterface {
  
  public function __construct(
    private ImageRepositoryInterface $repository,
  ) {
  }
  
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function __invoke(GetImageByIdQuery $query): Item|ImageView {
    if(!Uuid::isValid($query->getId())) {
      throw new NotFoundException();
    }
    
    $imageView = $this->repository->oneById($query->getId());
    
    return Item::fromPayload(ImageView::class, [
      ...$imageView->toPayload(),
      'url' => implode('/',
        [
          '/image',
          $imageView->getAttributes()->getFileName()
        ]),
    ]);
  }
}