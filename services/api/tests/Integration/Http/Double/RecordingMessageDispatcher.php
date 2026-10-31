<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Double;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;

final class RecordingMessageDispatcher implements MessageDispatcher {
  /** @var list<Message> */
  public array $messages = [];

  #[\Override]
  public function dispatch(Message ...$messages): void {
    \array_push($this->messages, ...$messages);
  }
}
