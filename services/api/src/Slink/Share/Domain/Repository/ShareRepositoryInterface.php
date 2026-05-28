<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Filter\ShareListFilter;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;

interface ShareRepositoryInterface {
  public function add(ShareView $share): void;

  public function remove(ShareView $share): void;

  public function findById(string $id): ?ShareView;

  public function findByTargetPath(TargetPath $targetPath): ?ShareView;

  public function findByShareable(string $shareableId, ShareableType $shareableType): ?ShareView;

  public function removeByShareable(string $shareableId, ShareableType $shareableType): void;

  /** @return ShareView[] */
  public function findAllByShareable(string $shareableId, ShareableType $shareableType): array;

  /** @return iterable<ShareView> */
  public function findAllUnpublished(): iterable;

  /**
   * @return Paginator<ShareView>
   */
  public function getShareList(ShareListFilter $filter): Paginator;

  public function countShareList(ShareListFilter $filter): int;

  public function existsByFilter(ShareListFilter $filter): bool;
}
