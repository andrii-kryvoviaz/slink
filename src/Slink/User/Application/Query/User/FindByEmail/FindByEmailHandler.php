<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\User\FindByEmail;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final readonly class FindByEmailHandler implements QueryHandlerInterface {
  public function __construct(private UserRepositoryInterface $repository) {
  }
  
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function __invoke(FindByEmailQuery $query): Item {
    $email = Email::fromString($query->getEmail());
    $userView = $this->repository->oneByEmail($email);

    return Item::fromEntity($userView);
  }
}