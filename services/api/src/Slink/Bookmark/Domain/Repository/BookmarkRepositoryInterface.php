<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Bookmark\Domain\ValueObject\BookmarkStatus;
use Slink\Bookmark\Infrastructure\ReadModel\View\BookmarkView;

interface BookmarkRepositoryInterface extends ServiceEntityRepositoryInterface {
  public function add(BookmarkView $bookmark): void;

  public function remove(BookmarkView $bookmark): void;

  public function oneById(string $id): BookmarkView;

  public function findById(string $id): ?BookmarkView;

  public function findByUserIdAndImageId(string $userId, string $imageId): ?BookmarkView;

  public function findByUserId(string $userId, int $page, int $limit, ?string $cursor = null): Paginator;

  public function countByImageId(string $imageId): int;

  public function isBookmarkedByUser(string $imageId, string $userId): bool;

  public function getBookmarkStatus(string $imageId, string $userId): BookmarkStatus;

  /**
   * @param array<string> $imageIds
   * @return array<string>
   */
  public function getBookmarkedImageIds(string $userId, array $imageIds): array;

  public function findByImageId(string $imageId, int $page, int $limit, ?string $cursor = null): Paginator;
}
