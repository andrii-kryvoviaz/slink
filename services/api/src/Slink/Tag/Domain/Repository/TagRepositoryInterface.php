<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Tag\Domain\Filter\TagListFilter;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;

interface TagRepositoryInterface extends ServiceEntityRepositoryInterface {
  public function add(TagView $tag): void;

  public function remove(TagView $tag): void;

  /**
   * @param ID $id
   * @return TagView
   * @throws NotFoundException
   * @throws NonUniqueResultException
   */
  public function oneById(ID $id): TagView;

  /**
   * @param string $name
   * @param ID $userId
   * @param ID|null $parentId
   * @return TagView|null
   * @throws NonUniqueResultException
   */
  public function findByNameAndParent(string $name, ID $userId, ?ID $parentId = null): ?TagView;

  /**
   * @param string $path
   * @param ID $userId
   * @return TagView|null
   * @throws NonUniqueResultException
   */
  public function findByPathAndUser(string $path, ID $userId): ?TagView;

  /**
   * @param int $page
   * @param TagListFilter $filter
   * @return Paginator<TagView>
   */
  public function getAllByPage(int $page, TagListFilter $filter): Paginator;

  /**
   * @param ID $userId
   * @return TagView[]
   */
  public function findByUserId(ID $userId): array;

  /**
   * @param ID $parentId
   * @param ID $userId
   * @return TagView[]
   */
  public function findChildren(ID $parentId, ID $userId): array;

  /**
   * @param ID $userId
   * @return TagView[]
   */
  public function findRootTags(ID $userId): array;

  /**
   * @param ID $imageId
   * @return TagView[]
   */
  public function findByImageId(ID $imageId): array;

  /**
   * @param array<string> $tagIds
   * @param ID $userId
   * @return TagView[]
   */
  public function findExactTagsByIds(array $tagIds, ID $userId): array;

  /**
   * @param ID[] $parentIds
   * @return TagView[]
   */
  public function findByParentIds(array $parentIds): array;

  /**
   * @param array<string> $tagPaths
   * @param ID $userId
   * @return TagView[]
   */
  public function findDescendantsByPaths(array $tagPaths, ID $userId): array;

  /**
   * @param array<string> $tagIds
   * @param ID $userId
   * @return TagView[]
   */
  public function findTagsWithDescendants(array $tagIds, ID $userId): array;
}