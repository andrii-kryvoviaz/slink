<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Double;

use Slink\Shared\Domain\Service\ServerSentEventPublisherInterface;

final class RecordingFeedPublisher implements ServerSentEventPublisherInterface {
  /** @var list<array{topic: string, data: array<string, mixed>, private: bool}> */
  public array $messages = [];
  public bool $fail = false;
  public bool $killAfterPublish = false;
  public ?string $journal = null;

  public function publish(string $topic, array $data, bool $private = false): void {
    if ($this->fail) {
      throw new \RuntimeException('Mercure unavailable');
    }
    $message = ['topic' => $topic, 'data' => $data, 'private' => $private];
    $this->messages[] = $message;
    if ($this->journal !== null) {
      file_put_contents($this->journal, json_encode($message, JSON_THROW_ON_ERROR));
    }
    if ($this->killAfterPublish) {
      posix_kill(posix_getpid(), SIGKILL);
    }
  }
}
