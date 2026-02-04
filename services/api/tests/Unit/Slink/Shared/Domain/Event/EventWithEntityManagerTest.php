<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Event;

use Doctrine\ORM\EntityManagerInterface;
use EventSauce\EventSourcing\Serialization\SerializablePayload;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Slink\Shared\Domain\Event\EventWithEntityManager;

final class EventWithEntityManagerTest extends TestCase {

  #[Test]
  public function itDecoratesEventWithEntityManager(): void {
    $originalEvent = $this->createMockEvent(['key' => 'value']);
    $entityManager = $this->createStub(EntityManagerInterface::class);

    $decoratedEvent = EventWithEntityManager::decorate($originalEvent, $entityManager);

    $this->assertInstanceOf(EventWithEntityManager::class, $decoratedEvent);
    $this->assertSame($originalEvent, $decoratedEvent->getEvent());
    $this->assertSame($entityManager, $decoratedEvent->getEntityManager());
  }

  #[Test]
  public function itReturnsOriginalEventPayload(): void {
    $payload = ['key' => 'value', 'data' => 123];
    $originalEvent = $this->createMockEvent($payload);
    $entityManager = $this->createStub(EntityManagerInterface::class);

    $decoratedEvent = EventWithEntityManager::decorate($originalEvent, $entityManager);

    $this->assertEquals($payload, $decoratedEvent->toPayload());
  }

  #[Test]
  public function itImplementsSerializablePayload(): void {
    $originalEvent = $this->createMockEvent([]);
    $entityManager = $this->createStub(EntityManagerInterface::class);

    $decoratedEvent = EventWithEntityManager::decorate($originalEvent, $entityManager);

    $this->assertInstanceOf(SerializablePayload::class, $decoratedEvent);
  }

  #[Test]
  public function itCreatesFromPayload(): void {
    $mockEvent = $this->createMockEvent(['test' => 'data']);
    $mockEntityManager = $this->createStub(EntityManagerInterface::class);

    $payload = [
      'event' => $mockEvent,
      'em' => $mockEntityManager
    ];

    $eventWithEntityManager = EventWithEntityManager::fromPayload($payload);

    $this->assertInstanceOf(EventWithEntityManager::class, $eventWithEntityManager);
    $this->assertSame($mockEvent, $eventWithEntityManager->getEvent());
    $this->assertSame($mockEntityManager, $eventWithEntityManager->getEntityManager());
  }

  #[Test]
  public function itIsReadonlyClass(): void {
    $reflection = new ReflectionClass(EventWithEntityManager::class);

    $this->assertTrue($reflection->hasProperty('event'));
    $this->assertTrue($reflection->hasProperty('em'));

    $eventProperty = $reflection->getProperty('event');
    $emProperty = $reflection->getProperty('em');

    $this->assertTrue($eventProperty->isPrivate());
    $this->assertTrue($emProperty->isPrivate());
  }

  #[Test]
  public function itHandlesComplexEventPayload(): void {
    $complexPayload = [
      'user_id' => 'user-123',
      'action' => 'update',
      'metadata' => [
        'timestamp' => '2024-01-01T00:00:00Z',
        'version' => 2
      ],
      'data' => [
        'name' => 'John Doe',
        'email' => 'john@example.com'
      ]
    ];

    $originalEvent = $this->createMockEvent($complexPayload);
    $entityManager = $this->createStub(EntityManagerInterface::class);

    $decoratedEvent = EventWithEntityManager::decorate($originalEvent, $entityManager);

    $this->assertEquals($complexPayload, $decoratedEvent->toPayload());
  }

  #[Test]
  public function itHandlesEmptyPayload(): void {
    $originalEvent = $this->createMockEvent([]);
    $entityManager = $this->createStub(EntityManagerInterface::class);

    $decoratedEvent = EventWithEntityManager::decorate($originalEvent, $entityManager);

    $this->assertEquals([], $decoratedEvent->toPayload());
  }

  #[Test]
  public function itMaintainsEventIdentity(): void {
    $originalEvent = $this->createMockEvent(['id' => 'event-123']);
    $entityManager = $this->createStub(EntityManagerInterface::class);

    $decoratedEvent = EventWithEntityManager::decorate($originalEvent, $entityManager);

    $this->assertSame($originalEvent, $decoratedEvent->getEvent());
    $this->assertNotSame($decoratedEvent, $originalEvent);
  }

  #[Test]
  public function itMaintainsEntityManagerIdentity(): void {
    $originalEvent = $this->createMockEvent([]);
    $entityManager = $this->createStub(EntityManagerInterface::class);

    $decoratedEvent = EventWithEntityManager::decorate($originalEvent, $entityManager);

    $this->assertSame($entityManager, $decoratedEvent->getEntityManager());
  }

  /**
   * @param array<string, mixed> $payload
   */
  private function createMockEvent(array $payload): SerializablePayload {
    $event = $this->createStub(SerializablePayload::class);
    $event->method('toPayload')->willReturn($payload);

    return $event;
  }
}
