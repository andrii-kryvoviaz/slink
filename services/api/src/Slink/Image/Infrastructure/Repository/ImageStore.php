<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Repository;

use EventSauce\EventSourcing\MessageDispatcher;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractViewBackedStore;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;

/**
 * @extends AbstractViewBackedStore<Image, ImageView>
 */
final class ImageStore extends AbstractViewBackedStore implements ImageStoreRepositoryInterface {
  public function __construct(
    MessageDispatcher $messageDispatcher,
    private readonly ImageRepositoryInterface $imageRepository,
  ) {
    parent::__construct($messageDispatcher);
  }

  protected static function getAggregateRootClass(): string {
    return Image::class;
  }

  protected function findRootView(ID $id): object {
    return $this->imageRepository->oneById($id->toString());
  }

  protected function hydrate(object $view): object {
    $tagIds = array_map(
      static fn(TagView $tag): ID => ID::fromString($tag->getUuid()),
      $view->getTags()->toArray(),
    );

    return Image::fromView($view, $tagIds, $view->getLicense());
  }

  public function get(ID $id): Image {
    return $this->retrieve($id);
  }

  public function store(Image $image): void {
    $this->persist($image);
  }
}
