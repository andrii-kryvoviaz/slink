<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Notification\Application\Command\MarkNotificationRead\MarkNotificationReadCommand;
use Slink\Notification\Domain\Filter\NotificationListFilter;
use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class DemoNotificationSeeder {
  use CommandTrait;

  public function __construct(
    private readonly NotificationRepositoryInterface $notificationRepository,
  ) {
  }

  /**
   * @return list<string>
   */
  public function unreadIds(UserView $user): array {
    return array_map(static fn(NotificationView $notification): string => $notification->getId(), $this->unread($user));
  }

  /**
   * @param list<string> $previousIds
   */
  public function markReadBefore(UserView $user, array $previousIds, \DateTimeImmutable $cutoff, DemoSeedReport $report): void {
    foreach ($this->unread($user) as $notification) {
      if (in_array($notification->getId(), $previousIds, true) || $notification->getCreatedAt() >= $cutoff) {
        continue;
      }

      $label = sprintf('%s by %s marked read', $notification->getTypeValue(), $notification->getActor()?->getUsername());
      $report->attempt($label, fn() => $this->markRead($user, $notification, $label, $report));
    }
  }

  /**
   * @return list<NotificationView>
   */
  private function unread(UserView $user): array {
    $limit = max(1, $this->notificationRepository->countUnreadByUserId($user->getUuid()));
    $filter = new NotificationListFilter(unreadOnly: true);

    /** @var list<NotificationView> */
    return iterator_to_array($this->notificationRepository->findByUserId($user->getUuid(), $filter, 1, $limit), false);
  }

  private function markRead(UserView $user, NotificationView $notification, string $label, DemoSeedReport $report): void {
    $command = new MarkNotificationReadCommand();
    $this->handle($command->withContext(['notificationId' => $notification->getId(), 'userId' => $user->getUuid()]));
    $report->created($label);
  }
}
