<?php

declare(strict_types=1);

namespace Slik\Image\Application\Query\GetImageById;

use Doctrine\ORM\NonUniqueResultException;
use Ramsey\Uuid\Uuid;
use Slik\Image\Infrastructure\ReadModel\Repository\ImageRepository;
use Slik\Image\Infrastructure\ReadModel\View\ImageView;
use Slik\Shared\Application\Http\Item;
use Slik\Shared\Application\Query\QueryHandlerInterface;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
use Slik\Shared\Infrastructure\FileSystem\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class GetImageByIdHandler implements QueryHandlerInterface {
  private Request $request;
  
  public function __construct(
    private ImageRepository $repository,
    RequestStack $requestStack,
  ) {
    $this->request = $requestStack->getCurrentRequest();
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
    
    if(!$query->isWrapped()) {
      return $imageView;
    }
    
    return Item::fromPayload(ImageRepository::entityClass(), [
      ...$imageView->toPayload(),
      'url' => implode('/',
        [
          // it is not necessary when client is using the same domain and has a reverse proxy
          //$this->request->getSchemeAndHttpHost(),
          '/image',
          $imageView->getAttributes()->getFileName()
        ]),
    ]);
  }
}