<?php

declare(strict_types=1);

namespace Slink\Tag\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\Tag\Domain\Event\ImageWasTagged;
use Slink\Tag\Domain\Event\ImageWasUntagged;
use Slink\Tag\Domain\Event\TagWasCreated;
use Slink\Tag\Domain\Event\TagWasDeleted;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use Slink\User\Domain\Repository\UserRepositoryInterface;

final class TagProjection extends AbstractProjection {
  public function __construct(
    private readonly TagRepositoryInterface   $tagRepository,
    private readonly ImageRepositoryInterface $imageRepository,
    private readonly UserRepositoryInterface  $userRepository,
  ) {
  }

  public function handleTagWasCreated(TagWasCreated $event): void {
    $user = $this->userRepository->one($event->userId);

    $tagView = new TagView(
      $event->id->toString(),
      $event->name->getValue(),
      $event->path->getValue(),
      $event->userId->toString(),
      $event->parentId?->toString()
    );

    $tagView->setUser($user);

    if ($event->parentId) {
      try {
        $parent = $this->tagRepository->oneById($event->parentId->toString());
        $tagView->setParent($parent);
      } catch (NotFoundException) {
      }
    }

    $this->tagRepository->add($tagView);
  }

  public function handleTagWasDeleted(TagWasDeleted $event): void {
    try {
      $tagView = $this->tagRepository->oneById($event->id->toString());
      $this->tagRepository->remove($tagView);
    } catch (NotFoundException) {
    }
  }

  public function handleImageWasTagged(ImageWasTagged $event): void {
    try {
      $tagView = $this->tagRepository->oneById($event->tagId->toString());
      $imageView = $this->imageRepository->oneById($event->imageId->toString());

      $tagView->addImage($imageView);
    } catch (NotFoundException) {
    }
  }

  public function handleImageWasUntagged(ImageWasUntagged $event): void {
    try {
      $tagView = $this->tagRepository->oneById($event->tagId->toString());
      $imageView = $this->imageRepository->oneById($event->imageId->toString());

      $tagView->removeImage($imageView);
    } catch (NotFoundException) {
    }
  }
}