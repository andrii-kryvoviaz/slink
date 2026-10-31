<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\EventStore;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\PaginationCursor;
use Generator;

final class NoOpMessageRepository implements MessageRepository {
  #[\Override]
  public function persist(Message ...$messages): void {
  }

  #[\Override]
  public function retrieveAll(AggregateRootId $id): Generator {
    yield from [];

    return 0;
  }

  #[\Override]
  public function retrieveAllAfterVersion(AggregateRootId $id, int $aggregateRootVersion): Generator {
    yield from [];

    return 0;
  }

  #[\Override]
  public function paginate(PaginationCursor $cursor): Generator {
    yield from [];

    return $cursor;
  }
}
