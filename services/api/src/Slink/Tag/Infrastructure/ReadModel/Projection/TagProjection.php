<?php

declare(strict_types=1);

namespace Slink\Tag\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Slink\Image\Domain\Event\ImageWasTagged;
use Slink\Image\Domain\Event\ImageWasUntagged;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\Tag\Domain\Event\TagWasCreated;
use Slink\Tag\Domain\Event\TagWasDeleted;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\Shared\Domain\ValueObject\Date\DateTime;

final class TagProjection extends AbstractProjection {
  public function __construct(
    private readonly TagRepositoryInterface   $tagRepository,
    private readonly ImageRepositoryInterface $imageRepository,
    private readonly UserRepositoryInterface  $userRepository,
    private readonly EntityManagerInterface   $entityManager
  ) {
  }

  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function handleTagWasCreated(TagWasCreated $event): void {
    $user = $this->userRepository->one($event->userId);

    $createdAt = $event->createdAt ?? DateTime::now();

    $tagView = new TagView(
      $event->id->toString(),
      $event->name->getValue(),
      $event->path->getValue(),
      $event->userId->toString(),
      $event->parentId?->toString(),
      $createdAt,
      $createdAt
    );

    $tagView->setUser($user);

    if ($event->parentId) {
      $parent = $this->tagRepository->oneById($event->parentId->toString());
      $tagView->setParent($parent);
    }

    $this->tagRepository->add($tagView);
  }

  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function handleTagWasDeleted(TagWasDeleted $event): void {
    $tagView = $this->tagRepository->oneById($event->id->toString());
    $this->tagRepository->remove($tagView);
  }

  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   * @throws ORMException
   */
  public function handleImageWasTagged(ImageWasTagged $event): void {
    $imageView = $this->imageRepository->oneById($event->imageId->toString());
    $tagView = $this->entityManager->getReference(TagView::class, $event->tagId->toString());

    $imageView->addTag($tagView);
    $this->imageRepository->add($imageView);
  }

  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   * @throws ORMException
   */
  public function handleImageWasUntagged(ImageWasUntagged $event): void {
    $imageView = $this->imageRepository->oneById($event->imageId->toString());
    $tagView = $this->entityManager->getReference(TagView::class, $event->tagId->toString());

    $imageView->removeTag($tagView);
    $this->imageRepository->add($imageView);
  }
}