<?php

declare(strict_types=1);

namespace Slink\Tag\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Override;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Pagination\CursorPaginationTrait;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;
use Slink\Tag\Domain\Filter\TagListFilter;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;

final class TagRepository extends AbstractRepository implements TagRepositoryInterface {
  use CursorPaginationTrait;

  static protected function entityClass(): string {
    return TagView::class;
  }

  public function add(TagView $tag): void {
    $this->_em->persist($tag);
  }

  public function remove(TagView $tag): void {
    $this->_em->remove($tag);
  }

  /**
   * @throws NotFoundException
   * @throws NonUniqueResultException
   */
  public function oneById(string $id): TagView {
    $qb = $this->createQueryBuilder('t')
      ->where('t.uuid = :id')
      ->setParameter('id', $id);

    $tag = $qb->getQuery()->getOneOrNullResult();

    if (!$tag instanceof TagView) {
      throw new NotFoundException();
    }

    return $tag;
  }

  /**
   * @throws NonUniqueResultException
   */
  public function findByNameAndParent(string $name, ID $userId, ?ID $parentId = null): ?TagView {
    $qb = $this->createQueryBuilder('t')
      ->where('t.name = :name')
      ->andWhere('t.userId = :userId')
      ->setParameter('name', $name)
      ->setParameter('userId', $userId->toString());

    if ($parentId) {
      $qb->andWhere('t.parentId = :parentId')
        ->setParameter('parentId', $parentId->toString());
    } else {
      $qb->andWhere('t.parentId IS NULL');
    }

    return $qb->getQuery()->getOneOrNullResult();
  }

  /**
   * @throws NonUniqueResultException
   */
  public function findByPathAndUser(string $path, ID $userId): ?TagView {
    return $this->createQueryBuilder('t')
      ->where('t.path = :path')
      ->andWhere('t.userId = :userId')
      ->setParameter('path', $path)
      ->setParameter('userId', $userId->toString())
      ->getQuery()
      ->getOneOrNullResult();
  }

  public function getAllByPage(int $page, TagListFilter $filter): Paginator {
    $qb = $this->createQueryBuilder('t')
      ->leftJoin('t.user', 'u')
      ->addSelect('u');

    if ($filter->getUserId()) {
      $qb->andWhere('t.userId = :userId')
        ->setParameter('userId', $filter->getUserId());
    }

    if ($filter->getParentId()) {
      $qb->andWhere('t.parentId = :parentId')
        ->setParameter('parentId', $filter->getParentId());
    }

    if ($filter->isRootOnly()) {
      $qb->andWhere('t.parentId IS NULL');
    }

    if ($filter->getSearchTerm()) {
      if (!$filter->getUserId()) {
        throw new \InvalidArgumentException('User ID is required when searching tags');
      }
      $qb->andWhere('t.userId = :searchUserId AND (t.name LIKE :searchTerm OR t.path LIKE :searchTerm)')
        ->setParameter('searchTerm', '%' . $filter->getSearchTerm() . '%')
        ->setParameter('searchUserId', $filter->getUserId());
    }

    $orderBy = $filter->getOrderBy() ?: 'name';
    $order = $filter->getOrder() ?: 'asc';
    $qb->orderBy("t.$orderBy", $order);

    return $this->paginate($qb, $page, $filter->getLimit() ?: 50);
  }

  public function findByUserId(ID $userId): array {
    return $this->createQueryBuilder('t')
      ->where('t.userId = :userId')
      ->setParameter('userId', $userId->toString())
      ->orderBy('t.path', 'ASC')
      ->getQuery()
      ->getResult();
  }

  public function findChildren(ID $parentId, ID $userId): array {
    return $this->createQueryBuilder('t')
      ->where('t.parentId = :parentId')
      ->andWhere('t.userId = :userId')
      ->setParameter('parentId', $parentId->toString())
      ->setParameter('userId', $userId->toString())
      ->orderBy('t.name', 'ASC')
      ->getQuery()
      ->getResult();
  }

  public function findRootTags(ID $userId): array {
    return $this->createQueryBuilder('t')
      ->where('t.parentId IS NULL')
      ->andWhere('t.userId = :userId')
      ->setParameter('userId', $userId->toString())
      ->orderBy('t.name', 'ASC')
      ->getQuery()
      ->getResult();
  }

  public function findByImageId(ID $imageId): array {
    return $this->createQueryBuilder('t')
      ->join('t.images', 'i')
      ->where('i.uuid = :imageId')
      ->setParameter('imageId', $imageId->toString())
      ->orderBy('t.path', 'ASC')
      ->getQuery()
      ->getResult();
  }

  public function findByIds(array $tagIds, ID $userId): array {
    if (empty($tagIds)) {
      return [];
    }

    return $this->createQueryBuilder('t')
      ->where('t.uuid IN (:tagIds)')
      ->andWhere('t.userId = :userId')
      ->setParameter('tagIds', $tagIds)
      ->setParameter('userId', $userId->toString())
      ->orderBy('t.path', 'ASC')
      ->getQuery()
      ->getResult();
  }

  public function findByParentIds(array $parentIds): array {
    if (empty($parentIds)) {
      return [];
    }

    $parentIdStrings = array_map(fn(ID $id) => $id->toString(), $parentIds);

    return $this->createQueryBuilder('t')
      ->where('t.parentId IN (:parentIds)')
      ->setParameter('parentIds', $parentIdStrings)
      ->orderBy('t.path', 'ASC')
      ->getQuery()
      ->getResult();
  }

  public function findDescendantsByPaths(array $tagPaths, ID $userId): array {
    if (empty($tagPaths)) {
      return [];
    }

    $qb = $this->createQueryBuilder('t')
      ->where('t.userId = :userId')
      ->setParameter('userId', $userId->toString());

    $orConditions = [];
    foreach ($tagPaths as $index => $tagPath) {
      $paramName = "path_$index";
      $orConditions[] = "t.path LIKE :$paramName";
      $qb->setParameter($paramName, $tagPath . '/%');
    }

    $qb->andWhere('(' . implode(' OR ', $orConditions) . ')');

    return $qb->orderBy('t.path', 'ASC')
      ->getQuery()
      ->getResult();
  }

  public function findByTagIds(array $tagIds, ID $userId): array {
    if (empty($tagIds)) {
      return [];
    }

    $tags = $this->findByIds($tagIds, $userId);
    $tagPaths = array_map(fn($tag) => $tag->getPath(), $tags);
    
    if (empty($tagPaths)) {
      return [];
    }

    $descendants = $this->findDescendantsByPaths($tagPaths, $userId);
    $allTags = array_merge($tags, $descendants);

    $uniqueTags = [];
    $seenIds = [];
    
    foreach ($allTags as $tag) {
      if (!in_array($tag->getUuid(), $seenIds)) {
        $uniqueTags[] = $tag;
        $seenIds[] = $tag->getUuid();
      }
    }

    return $uniqueTags;
  }
}