<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetCollectionScopedImageContent;

use Slink\Collection\Domain\Enum\CollectionScopedImageAccess;
use Slink\Image\Application\Service\ImageContentLoader;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Http\CachePolicy;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class GetCollectionScopedImageContentHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageRepositoryInterface $repository,
    private AuthorizationCheckerInterface $access,
    private ImageContentLoader $loader,
    private ConfigurationProviderInterface $configurationProvider,
    #[Autowire('%image.public_max_width%')]
    private int $maxWidth,
  ) {
  }

  /**
   * @throws NotFoundException
   */
  public function __invoke(GetCollectionScopedImageContentQuery $query): Item {
    $imageView = $this->repository->oneById($query->itemId);

    if (!$this->access->isGranted(CollectionScopedImageAccess::View, $query->toAccessContext($imageView))) {
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
