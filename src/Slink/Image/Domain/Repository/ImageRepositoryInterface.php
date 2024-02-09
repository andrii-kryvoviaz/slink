<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

interface ImageRepositoryInterface extends ServiceEntityRepositoryInterface {
  public function add(ImageView $image): void;
  
  /**
   * @throws NotFoundException
   * @throws NonUniqueResultException
   */
  public function oneById(string $id): ImageView;
}