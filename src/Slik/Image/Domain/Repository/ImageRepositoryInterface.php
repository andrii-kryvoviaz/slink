<?php

declare(strict_types=1);

namespace Slik\Image\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use Slik\Image\Infrastructure\ReadModel\View\ImageView;
use Slik\Shared\Infrastructure\Exception\NotFoundException;

interface ImageRepositoryInterface extends ServiceEntityRepositoryInterface {
  public function add(ImageView $image): void;
  
  /**
   * @throws NotFoundException
   * @throws NonUniqueResultException
   */
  public function oneById(string $id): ImageView;
}