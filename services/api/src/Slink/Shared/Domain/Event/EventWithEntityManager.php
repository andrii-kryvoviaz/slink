<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Event;

use Doctrine\ORM\EntityManagerInterface;
use EventSauce\EventSourcing\Serialization\SerializablePayload;

final readonly class EventWithEntityManager implements SerializablePayload {
  
  /**
   * @param SerializablePayload $event
   * @param EntityManagerInterface $em
   */
  private function __construct(
    private SerializablePayload    $event,
    private EntityManagerInterface $em
  ) {
  }
  
  /**
   * @param SerializablePayload $event
   * @param EntityManagerInterface $em
   * @return self
   */
  public static function decorate(SerializablePayload $event, EntityManagerInterface $em): self {
    return new self($event, $em);
  }
  
  /**
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return $this->event->toPayload();
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['event'],
      $payload['em']
    );
  }
  
  /**
   * @return SerializablePayload
   */
  public function getEvent(): SerializablePayload {
    return $this->event;
  }
  
  /**
   * @return EntityManagerInterface
   */
  public function getEntityManager(): EntityManagerInterface {
    return $this->em;
  }
}