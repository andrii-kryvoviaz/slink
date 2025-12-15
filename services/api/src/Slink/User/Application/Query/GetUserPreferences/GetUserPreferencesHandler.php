<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\GetUserPreferences;

use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\User\Domain\ValueObject\UserPreferences;
use Slink\User\Infrastructure\ReadModel\Repository\UserPreferencesRepository;

final readonly class GetUserPreferencesHandler implements QueryHandlerInterface {
  public function __construct(
    private UserPreferencesRepository $repository,
  ) {
  }

  public function __invoke(GetUserPreferencesQuery $query): Item {
    $prefsView = $this->repository->findByUserId($query->getUserId());
    $prefs = $prefsView?->getPreferences() ?? UserPreferences::empty();
    
    return Item::fromPayload('preferences', $prefs->toPayload());
  }
}
