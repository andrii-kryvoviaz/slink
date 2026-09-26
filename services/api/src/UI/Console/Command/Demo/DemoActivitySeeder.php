<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Clock\Clock;

final class DemoActivitySeeder {
  private const string WINDOW = '-21 days';
  private const string RECENT_WINDOW = '-48 hours';
  private const int RECENT_NOTIFICATIONS = 8;

  public function __construct(
    private readonly DemoSession $session,
    private readonly DemoBookmarkSeeder $bookmarkSeeder,
    private readonly DemoCommentSeeder $commentSeeder,
    private readonly DemoNotificationSeeder $notificationSeeder,
  ) {
  }

  /**
   * @param array<string, UserView> $accounts
   * @param array<string, ImageView> $images
   * @param array<string, ImageView> $accountImages
   * @return list<DemoSeedReport>
   */
  public function seed(UserView $demoUser, array $accounts, array $images, array $accountImages): array {
    $bookmarks = new DemoSeedReport('Bookmarks');
    $comments = new DemoSeedReport('Comments');
    $notifications = new DemoSeedReport('Notifications');

    $activities = $this->interleave(
      $this->commentSeeder->threads($demoUser, $accounts, $images, $comments),
      $this->bookmarkSeeder->activities($demoUser, $accounts, $images, $accountImages, $bookmarks),
    );
    $previousIds = $this->notificationSeeder->unreadIds($demoUser);
    $now = Clock::get()->now();

    $this->replay($activities, $demoUser, $now);
    $this->notificationSeeder->markReadBefore($demoUser, $previousIds, $now->modify(self::RECENT_WINDOW), $notifications);

    return [$bookmarks, $comments, $notifications];
  }

  /**
   * @param list<list<array{UserView, \Closure(): mixed}>> $threads
   * @param list<array{UserView, \Closure(): mixed}> $bookmarks
   * @return list<array{UserView, \Closure(): mixed}>
   */
  private function interleave(array $threads, array $bookmarks): array {
    $activities = [];

    foreach ($threads as $index => $thread) {
      $leading = array_splice($bookmarks, 0, intdiv(count($bookmarks), count($threads) - $index));
      $activities = [...$activities, ...$leading, ...$thread];
    }

    return [...$activities, ...$bookmarks];
  }

  /**
   * @param list<array{UserView, \Closure(): mixed}> $activities
   */
  private function replay(array $activities, UserView $demoUser, \DateTimeImmutable $now): void {
    $recentStart = $this->recentStart($activities, $demoUser);

    foreach ($activities as $index => [$actor, $action]) {
      $moment = $this->momentOf($index, $recentStart, count($activities), $now);
      $this->session->at($moment, fn() => $this->session->actingAs($actor, $action));
    }
  }

  /**
   * @param list<array{UserView, \Closure(): mixed}> $activities
   */
  private function recentStart(array $activities, UserView $demoUser): int {
    $notifying = array_keys(array_filter(
      $activities,
      static fn(array $activity): bool => $activity[0]->getUuid() !== $demoUser->getUuid(),
    ));

    return array_slice($notifying, -self::RECENT_NOTIFICATIONS, 1)[0] ?? 0;
  }

  private function momentOf(int $index, int $recentStart, int $count, \DateTimeImmutable $now): \DateTimeImmutable {
    $recentFrom = $now->modify(self::RECENT_WINDOW);

    if ($index < $recentStart) {
      return $this->spread($now->modify(self::WINDOW), $recentFrom, $index, $recentStart);
    }

    return $this->spread($recentFrom, $now, $index - $recentStart, $count - $recentStart);
  }

  private function spread(\DateTimeImmutable $from, \DateTimeImmutable $to, int $position, int $slots): \DateTimeImmutable {
    $offset = intdiv((2 * $position + 1) * ($to->getTimestamp() - $from->getTimestamp()), 2 * $slots);

    return $from->modify(sprintf('+%d seconds', $offset));
  }
}
