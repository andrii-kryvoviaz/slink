<?php

declare(strict_types=1);

namespace Slik\User\Application\Query\User\FindByEmail;

use Doctrine\ORM\NonUniqueResultException;
use Slik\Shared\Application\Http\Item;
use Slik\Shared\Application\Query\QueryHandlerInterface;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
use Slik\User\Domain\ValueObject\Email;
use Slik\User\Infrastructure\ReadModel\Repository\UserRepository;

final readonly class FindByEmailHandler implements QueryHandlerInterface {
  public function __construct(private UserRepository $repository) {
  }
  
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function __invoke(FindByEmailQuery $query): Item {
    $email = Email::fromString($query->getEmail());
    $userView = $this->repository->oneByEmail($email);

    return Item::fromPayload(UserRepository::entityClass(), $userView);
  }
}