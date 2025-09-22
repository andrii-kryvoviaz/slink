<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

interface ImageRepositoryInterface extends ServiceEntityRepositoryInterface {
  public function add(ImageView $image): void;

  public function remove(ImageView $image): void;

  /**
   * @throws NotFoundException
   * @throws NonUniqueResultException
   */
  public function oneById(string $id): ImageView;

  /**
   * @param string $fileName
   * @return ImageView
   */
  public function oneByFileName(string $fileName): ImageView;

  /**
   * @param string $sha1Hash
   * @param ID|null $userId
   * @return ImageView|null
   */
  public function findBySha1Hash(string $sha1Hash, ?ID $userId = null): ?ImageView;

  /**
   * @return ImageView[]
   */
  public function findImagesWithoutSha1Hash(): array;

  /**
   * @param int $page
   * @param ImageListFilter $imageListFilter
   * @return Paginator<ImageView>
   */
  public function geImageList(int $page, ImageListFilter $imageListFilter): Paginator;

  /**
   * @param ID $userId
   * @return ImageView[]
   */
  public function findByUserId(ID $userId): array;
}