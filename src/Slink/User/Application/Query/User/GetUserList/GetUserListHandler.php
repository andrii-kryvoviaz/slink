<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\User\GetUserList;

use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\User\Domain\Filter\UserListFilter;
use Slink\User\Domain\Repository\UserRepositoryInterface;

final readonly class GetUserListHandler implements QueryHandlerInterface {
  /**
   * @param UserRepositoryInterface $repository
   */
  public function __construct(
    private UserRepositoryInterface $repository
  ) {
  }
  
  /**
   * @param GetUserListQuery $query
   * @param int $page
   * @param array<string> $groups
   * @return Collection
   */
  public function __invoke(GetUserListQuery $query, int $page = 1, array $groups = ['public']): Collection {
    $users = $this->repository->getUserList($page, UserListFilter::fromQuery($query));
    
    $items = array_map(fn($user) => Item::fromEntity($user, groups: $groups), iterator_to_array($users));
    
    return new Collection(
      $page,
      $query->getLimit(),
      $users->count(),
      $items
    );
  }
}