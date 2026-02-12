<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Infrastructure\EventConsumer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Application\Command\DeleteTag\DeleteTagCommand;
use Slink\Tag\Domain\Event\TagWasDeleted;
use Slink\Tag\Infrastructure\EventConsumer\TagCascadeDeleteConsumer;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;

final class TagCascadeDeleteConsumerTest extends TestCase {

  #[Test]
  public function itDispatchesDeleteCommandForEachChild(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $consumer = new TagCascadeDeleteConsumer($commandBus);

    $commandBus->expects($this->exactly(2))
      ->method('handle')
      ->with($this->isInstanceOf(Envelope::class));

    $event = new TagWasDeleted(ID::generate(), 'user-1', ['child-1', 'child-2']);
    $consumer->handleTagWasDeleted($event);
  }

  #[Test]
  public function itDoesNothingWhenNoChildren(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $consumer = new TagCascadeDeleteConsumer($commandBus);

    $commandBus->expects($this->never())->method('handle');

    $event = new TagWasDeleted(ID::generate(), 'user-1', []);
    $consumer->handleTagWasDeleted($event);
  }

  #[Test]
  public function itPassesUserIdInCommandContext(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $consumer = new TagCascadeDeleteConsumer($commandBus);

    $commandBus->expects($this->once())
      ->method('handle')
      ->with($this->callback(function (Envelope $envelope): bool {
        $message = $envelope->getMessage();
        if (!$message instanceof DeleteTagCommand) {
          return false;
        }

        if ($message->getId() !== 'child-1') {
          return false;
        }

        $stamp = $envelope->last(HandlerArgumentsStamp::class);
        if (!$stamp instanceof HandlerArgumentsStamp) {
          return false;
        }

        $context = $stamp->getAdditionalArguments();
        return ($context['userId'] ?? null) === 'user-1';
      }));

    $event = new TagWasDeleted(ID::generate(), 'user-1', ['child-1']);
    $consumer->handleTagWasDeleted($event);
  }
}
