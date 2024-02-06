<?php

declare(strict_types=1);

namespace Slik\Image\Application\Query\GetImageById;

use Doctrine\ORM\NonUniqueResultException;
use Ramsey\Uuid\Uuid;
use Slik\Image\Domain\Repository\ImageRepositoryInterface;
use Slik\Image\Infrastructure\ReadModel\View\ImageView;
use Slik\Shared\Application\Http\Item;
use Slik\Shared\Application\Query\QueryHandlerInterface;
use Slik\Shared\Infrastructure\Exception\NotFoundException;

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