<?php

declare(strict_types=1);

namespace Tests\Integration\Scheduler;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Scheduler\Trigger\CronExpressionTrigger;

final class ChunkedUploadGcScheduleTest extends KernelTestCase {
  private const string COMMAND_NAME = 'image:chunked-upload:gc';

  #[Test]
  public function itRegistersExactlyOneGcCommandOnDefaultSchedule(): void {
    self::assertCount(1, $this->gcRecurringMessages());
  }

  #[Test]
  public function itTriggersGcCommandHourlyAtMinuteZero(): void {
    $trigger = $this->gcRecurringMessages()[0]->getTrigger();

    self::assertInstanceOf(CronExpressionTrigger::class, $trigger);
    self::assertSame('0 * * * *', (string) $trigger);

    $nextRun = $trigger->getNextRunDate(new \DateTimeImmutable('2026-01-15 10:17:00', new \DateTimeZone('UTC')));

    self::assertNotNull($nextRun);
    self::assertSame('2026-01-15 11:00:00 UTC', $nextRun->format('Y-m-d H:i:s e'));
  }

  /**
   * @return list<RecurringMessage>
   */
  private function gcRecurringMessages(): array {
    $provider = static::getContainer()->get('scheduler.provider.default');

    self::assertInstanceOf(ScheduleProviderInterface::class, $provider);

    return \array_values(\array_filter(
      $provider->getSchedule()->getRecurringMessages(),
      fn (RecurringMessage $message): bool => $this->isGcCommandMessage($message),
    ));
  }

  private function isGcCommandMessage(RecurringMessage $message): bool {
    $context = new MessageContext('default', $message->getId(), $message->getTrigger(), new \DateTimeImmutable());

    foreach ($message->getMessages($context) as $innerMessage) {
      if ($innerMessage instanceof RunCommandMessage && $innerMessage->input === self::COMMAND_NAME) {
        return true;
      }
    }

    return false;
  }
}
