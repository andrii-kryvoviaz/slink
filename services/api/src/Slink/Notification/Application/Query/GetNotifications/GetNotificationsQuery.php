<?php

declare(strict_types=1);

namespace Slink\Notification\Application\Query\GetNotifications;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetNotificationsQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    #[Assert\Positive]
    private int $page = 1,

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(100)]
    private int $limit = 20,
  ) {
  }

  public function getPage(): int {
    return $this->page;
  }

  public function getLimit(): int {
    return $this->limit;
  }
}
