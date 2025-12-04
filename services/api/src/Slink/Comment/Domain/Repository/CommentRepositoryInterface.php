<?php

declare(strict_types=1);

namespace Slink\Comment\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Shared\Domain\ValueObject\ID;

interface CommentRepositoryInterface extends ServiceEntityRepositoryInterface {
  public function add(CommentView $comment): void;

  public function oneById(string $id): CommentView;

  public function findByImageId(string $imageId, int $page = 1, int $limit = 20): Paginator;

  public function countByImageId(string $imageId): int;
}
