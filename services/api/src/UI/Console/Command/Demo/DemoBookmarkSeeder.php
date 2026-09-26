<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Bookmark\Application\Command\AddBookmark\AddBookmarkCommand;
use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class DemoBookmarkSeeder {
  use CommandTrait;

  private const array BOOKMARKS = [
    'alice' => [
      'aaron-lau-EOnlL3L3IgQ-unsplash.jpg',
      'clay-banks-Icfk7F4Ku0w-unsplash.jpg',
      'malte-schmidt-enGr5YbjQKQ-unsplash.jpg',
      'nathan-dumlao-ciO5L8pin8A-unsplash.jpg',
      'biel-morro-bsSIk3LV_NE-unsplash.jpg',
      'jei-lee-yRXuXvy4sQ4-unsplash.jpg',
      'saffu-A7RzCegedb4-unsplash.jpg',
    ],
    'bob' => [
      'kazuend-2KXEb_8G5vo-unsplash.jpg',
      'nate-rayfield-_WR6tUIAJe8-unsplash.jpg',
      'jordan-steranka-lpddCskeg4A-unsplash.jpg',
      'piotr-pekala-t4cbHkeyXz4-unsplash.jpg',
      'samuel-ferrara-dKJXkKCF2D8-unsplash.jpg',
      'malte-schmidt-enGr5YbjQKQ-unsplash.jpg',
      'luca-bravo-WeFDiEDModQ-unsplash.jpg',
      'christian-grab-AygJgCM_vto-unsplash.jpg',
    ],
    'carol' => [
      'luca-bravo-WeFDiEDModQ-unsplash.jpg',
      'daniel-roe-lpjb_UMOyx8-unsplash.jpg',
      'kazuend-2KXEb_8G5vo-unsplash.jpg',
      'malte-schmidt-enGr5YbjQKQ-unsplash.jpg',
      'patrick-langwallner-wiFOaBrX_wc-unsplash.jpg',
      'nicolette-meade-RL3F99l0XYE-unsplash.jpg',
      'filip-zrnzevic-_EMkxLdko9k-unsplash.jpg',
    ],
  ];

  public function __construct(
    private readonly BookmarkRepositoryInterface $bookmarkRepository,
  ) {
  }

  /**
   * @param array<string, UserView> $accounts
   * @param array<string, ImageView> $images
   * @param array<string, ImageView> $accountImages
   * @return list<array{UserView, \Closure(): mixed}>
   */
  public function activities(UserView $demoUser, array $accounts, array $images, array $accountImages, DemoSeedReport $report): array {
    $activities = [];

    foreach ($accountImages as $label => $image) {
      $activities[] = $this->activity($demoUser, $image, $label, $report);
    }

    foreach (array_intersect_key(self::BOOKMARKS, $accounts) as $username => $fileNames) {
      foreach (array_intersect($fileNames, array_keys($images)) as $fileName) {
        $activities[] = $this->activity($accounts[$username], $images[$fileName], $fileName, $report);
      }
    }

    return $activities;
  }

  /**
   * @return array{UserView, \Closure(): mixed}
   */
  private function activity(UserView $actor, ImageView $image, string $imageLabel, DemoSeedReport $report): array {
    $label = sprintf('%s by %s', $imageLabel, $actor->getUsername());

    return [$actor, fn() => $report->attempt($label, fn() => $this->ensureBookmark($actor, $image, $label, $report))];
  }

  private function ensureBookmark(UserView $actor, ImageView $image, string $label, DemoSeedReport $report): void {
    if ($this->bookmarkRepository->isBookmarkedByUser($image->getUuid(), $actor->getUuid())) {
      $report->skipped($label);

      return;
    }

    $command = new AddBookmarkCommand($image->getUuid());
    $this->handle($command->withContext(['userId' => $actor->getUuid()]));
    $report->created($label);
  }
}
