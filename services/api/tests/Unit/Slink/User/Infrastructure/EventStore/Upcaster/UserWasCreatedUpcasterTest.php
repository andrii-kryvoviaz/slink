<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\EventStore\Upcaster;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Infrastructure\EventStore\Upcaster\UserWasCreatedUpcaster;

final class UserWasCreatedUpcasterTest extends TestCase {
  private const string EVENT_TYPE = 'slink.user.domain.event.user_was_created';

  /**
   * @return array<string, array{string, string}>
   */
  public static function provideStoredDisplayNames(): array {
    return [
      'Simple name' => ['John Doe', 'john.doe'],
      'Multiple spaces' => ['  John   Doe  ', 'john...doe'],
      'Apostrophe' => ["O'Neil Smith", "o'neil.smith"],
      'Accented characters' => ['José María', 'josé.maría'],
    ];
  }

  #[Test]
  #[DataProvider('provideStoredDisplayNames')]
  public function itKeepsDerivingUsernameOfStoredEventsAsWhenTheyWereRecorded(string $displayName, string $expectedUsername): void {
    $message = (new UserWasCreatedUpcaster())->upcast([
      'headers' => ['__event_type' => self::EVENT_TYPE],
      'payload' => ['displayName' => $displayName],
    ]);

    $this->assertSame($expectedUsername, $message['payload']['username']);
  }

  #[Test]
  public function itKeepsUsernameRecordedInTheEvent(): void {
    $message = (new UserWasCreatedUpcaster())->upcast([
      'headers' => ['__event_type' => self::EVENT_TYPE],
      'payload' => ['displayName' => 'John Doe', 'username' => 'johnny'],
    ]);

    $this->assertSame('johnny', $message['payload']['username']);
  }
}
