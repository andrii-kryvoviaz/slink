<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetPublicImageContent;

use Slink\Image\Application\Service\ImageContentLoader;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Http\CachePolicy;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class GetPublicImageContentHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageRepositoryInterface $repository,
    private ImageContentLoader $loader,
    private ConfigurationProviderInterface $configurationProvider,
    #[Autowire('%image.public_max_width%')]
    private int $maxWidth,
  ) {
  }

  /**
   * @throws NotFoundException
   */
  public function __invoke(GetPublicImageContentQuery $query): Item {
    $imageView = $this->repository->oneById($query->imageId);

    if (!$imageView->getAttributes()->isPublic()) {
      throw new NotFoundException();
    }

    $payload = $this->loader->load(
      $imageView,
      transforms: [
        'width' => $this->maxWidth,
        'quality' => $this->configurationProvider->get('image.compressionQuality'),
      ],
    );

    return Item::fromContent($payload->content, $payload->mimeType, CachePolicy::revocable());
  }
}
