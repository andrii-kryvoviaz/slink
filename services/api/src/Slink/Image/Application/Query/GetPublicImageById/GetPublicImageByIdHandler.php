<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetPublicImageById;

use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\Resource\ImageResourceContext;
use Slink\Image\Infrastructure\Resource\ImageResourceProcessor;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

final readonly class GetPublicImageByIdHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageRepositoryInterface $repository,
    private ImageResourceProcessor   $resourceProcessor,
  ) {
  }

  /**
   * @throws NotFoundException
   */
  public function __invoke(
    GetPublicImageByIdQuery $query,
    ?ImageResourceContext   $resourceContext = null,
  ): Item {
    $imageView = $this->repository->oneById($query->getId());

    if (!$imageView->getAttributes()->isPublic()) {
      throw new NotFoundException();
    }

    $resourceContext ??= new ImageResourceContext();

    return $this->resourceProcessor->one($imageView, $resourceContext);
  }
}
