<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\MessageBus;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;

trait EnvelopedMessage {
  
  /**
   * @param array<string, mixed>|\JsonSerializable $context
   * @return Envelope
   */
  public function withContext(array|\JsonSerializable $context): Envelope {
    if($context instanceof \JsonSerializable) {
      $context = $context->jsonSerialize();
    }
    
    return new Envelope($this, [new HandlerArgumentsStamp($context)]);
  }
}