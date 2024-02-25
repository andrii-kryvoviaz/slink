<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\User\FindUserById;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Domain\Repository\UserRepositoryInterface;

final readonly class FindUserByIdHandler implements QueryHandlerInterface {
  
  public function __construct(private UserRepositoryInterface $repository) {
  }
  
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function __invoke(FindUserByIdQuery $query): Item {
    $id = ID::fromString($query->getId());
    $userView = $this->repository->one($id);
    
    return Item::fromEntity($userView, groups: $query->getGroups());
  }
}