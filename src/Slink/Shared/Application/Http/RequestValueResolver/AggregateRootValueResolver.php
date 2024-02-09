<?php

namespace Slink\Shared\Application\Http\RequestValueResolver;

use Generator;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final readonly class AggregateRootValueResolver implements ValueResolverInterface {
  
  /**
   * @param MessageRepository $messageRepository
   */
  public function __construct(private MessageRepository $messageRepository) {
  }
  
  /**
   * @inheritDoc
   * @return iterable<AggregateRoot>
   */
  public function resolve(Request $request, ArgumentMetadata $argument): iterable {
    $argumentType = $argument->getType();
    if (
      !$argumentType
      || !is_subclass_of($argumentType, AggregateRoot::class, true)
    ) {
      return [];
    }
    
    $value = $request->attributes->get('id');
    if (!is_string($value)) {
      return [];
    }
    
    $uuid = ID::fromString($value);
    
    $events = $this->retrieveAllEvents($uuid);
    $aggregateRoot = $argumentType::reconstituteFromEvents($uuid, $events);
    
    return [$aggregateRoot];
  }
  
  /**
   * @param AggregateRootId $aggregateRootId
   * @return Generator
   */
  private function retrieveAllEvents(AggregateRootId $aggregateRootId): Generator {
    /** @var Generator<Message> $messages */
    $messages = $this->messageRepository->retrieveAll($aggregateRootId);
    
    foreach ($messages as $message) {
      yield $message->event();
    }
    
    return $messages->getReturn();
  }
}