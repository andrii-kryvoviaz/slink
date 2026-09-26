<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Comment\Application\Command\CreateComment\CreateCommentCommand;
use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Comment\Domain\ValueObject\CommentContent;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class DemoCommentSeeder {
  use CommandTrait;

  private const string DEMO = 'demo';

  private const array THREADS = [
    'kazuend-2KXEb_8G5vo-unsplash.jpg' => [
      ['bob', 'How long was the exposure on this? The stars are pin sharp.', null],
      [self::DEMO, '20 seconds at f/2.8, ISO 3200. Tripod on the jetty.', 0],
      ['bob', 'Adding that to my notes, thanks!', 1],
      ['carol', 'Kawaguchiko? I missed this view by one cloudy night.', null],
    ],
    'nathan-dumlao-ciO5L8pin8A-unsplash.jpg' => [
      ['alice', 'Black and white really suits this one.', null],
    ],
    'biel-morro-bsSIk3LV_NE-unsplash.jpg' => [
      ['alice', 'The eyes! Is she always this suspicious?', null],
      [self::DEMO, "Only when there's food on the table.", 0],
      ['alice', 'So always, then.', 1],
    ],
    'jei-lee-yRXuXvy4sQ4-unsplash.jpg' => [
      ['alice', 'Instant spring. Needed this today.', null],
    ],
    'luca-bravo-WeFDiEDModQ-unsplash.jpg' => [
      ['carol', 'Lago di Braies? I was there in June and the water was exactly this colour.', null],
      [self::DEMO, 'Yes, early morning before the boats go out.', 0],
      ['carol', 'Worth the 5am alarm.', 1],
    ],
    'nicolette-meade-RL3F99l0XYE-unsplash.jpg' => [
      ['carol', 'This looks like a page from a pressed flower album.', null],
    ],
    'piotr-pekala-t4cbHkeyXz4-unsplash.jpg' => [
      ['bob', 'That light on the ridge. Moody in the best way.', null],
      [self::DEMO, 'Ten minutes later it was pouring.', 0],
      ['bob', 'The best light always comes right before the rain.', 1],
    ],
    'saffu-A7RzCegedb4-unsplash.jpg' => [
      ['alice', 'So calm. Would love this as a print.', null],
    ],
    'nate-rayfield-_WR6tUIAJe8-unsplash.jpg' => [
      ['bob', 'Is that the core of the Milky Way? What month was this?', null],
      [self::DEMO, 'Late May, around 2am.', 0],
      ['bob', 'Circling May in my calendar.', 1],
    ],
    'samuel-ferrara-dKJXkKCF2D8-unsplash.jpg' => [
      ['bob', 'Those layers of blue ridges are my favourite kind of view.', null],
    ],
    'clay-banks-Icfk7F4Ku0w-unsplash.jpg' => [
      ['bob', 'Which rooftop is this? The view is unreal.', null],
      [self::DEMO, "A friend's office, I promised not to share the address.", 0],
      ['bob', 'Fair enough!', 1],
    ],
    'george-pisarevsky-EsxDnpNYfPA-unsplash.jpg' => [
      ['bob', 'Crabapple? The backlight makes it glow.', null],
    ],
    'daniel-roe-lpjb_UMOyx8-unsplash.jpg' => [
      ['carol', 'Lake Louise before the crowds. How early did you get there?', null],
      [self::DEMO, 'Sunrise, and it was already busy.', 0],
      ['carol', 'Still looks peaceful here.', 1],
    ],
    'patrick-langwallner-wiFOaBrX_wc-unsplash.jpg' => [
      ['carol', 'Drone? The texture of the water is amazing.', null],
      [self::DEMO, 'Yes, from about 80 metres up.', 0],
      ['carol', 'It looks like a painting.', 1],
    ],
    'aaron-lau-EOnlL3L3IgQ-unsplash.jpg' => [
      ['alice', 'Love the low angle, the road lines pull you straight into the bridge.', null],
      ['carol', 'Same thought. Feels like lying on the road.', 0],
    ],
    'malte-schmidt-enGr5YbjQKQ-unsplash.jpg' => [
      ['carol', 'Manhattanhenge! Did you have to fight the crowd for this spot?', null],
      [self::DEMO, 'Half of Midtown was standing in the street with me.', 0],
      ['alice', 'I was there too, two blocks down. Total chaos.', 1],
      ['carol', 'Worth it.', 1],
    ],
  ];

  public function __construct(
    private readonly CommentRepositoryInterface $commentRepository,
  ) {
  }

  /**
   * @param array<string, UserView> $accounts
   * @param array<string, ImageView> $images
   * @return list<list<array{UserView, \Closure(): mixed}>>
   */
  public function threads(UserView $demoUser, array $accounts, array $images, DemoSeedReport $report): array {
    $actors = [self::DEMO => $demoUser] + $accounts;
    $threads = [];

    foreach (array_intersect_key(self::THREADS, $images) as $fileName => $messages) {
      $threads[] = $this->thread($actors, $fileName, $images[$fileName], $messages, $report);
    }

    return $threads;
  }

  /**
   * @param array<string, UserView> $actors
   * @param list<array{string, string, ?int}> $messages
   * @return list<array{UserView, \Closure(): mixed}>
   */
  private function thread(array $actors, string $fileName, ImageView $image, array $messages, DemoSeedReport $report): array {
    $activities = [];

    foreach ($messages as [$author, $content, $parent]) {
      $actor = $actors[$author];
      $parentKey = $this->parentKey($actors, $messages, $parent);
      $label = sprintf('%s %s: %s', $fileName, $author, $content);
      $activities[] = [
        $actor,
        fn() => $report->attempt($label, fn() => $this->ensureComment($actor, $image, $content, $parentKey, $label, $report)),
      ];
    }

    return $activities;
  }

  /**
   * @param array<string, UserView> $actors
   * @param list<array{string, string, ?int}> $messages
   */
  private function parentKey(array $actors, array $messages, ?int $parent): ?string {
    if ($parent === null) {
      return null;
    }

    [$author, $content] = $messages[$parent];

    return $this->messageKey($actors[$author], $content);
  }

  private function ensureComment(UserView $actor, ImageView $image, string $content, ?string $parentKey, string $label, DemoSeedReport $report): void {
    $existing = $this->existingComments($image->getUuid());

    if (isset($existing[$this->messageKey($actor, $content)])) {
      $report->skipped($label);

      return;
    }

    $referencedCommentId = null;

    if ($parentKey !== null) {
      $referencedCommentId = $existing[$parentKey] ?? throw new \RuntimeException('Parent comment is missing');
    }

    $command = new CreateCommentCommand($content, $referencedCommentId);
    $this->handle($command->withContext(['imageId' => $image->getUuid(), 'userId' => $actor->getUuid()]));
    $report->created($label);
  }

  /**
   * @return array<string, string>
   */
  private function existingComments(string $imageId): array {
    $limit = max(1, $this->commentRepository->countByImageId($imageId));
    $comments = [];

    /** @var CommentView $comment */
    foreach ($this->commentRepository->findByImageId($imageId, 1, $limit) as $comment) {
      $comments[$this->key((string) $comment->getUserId(), $comment->getContent())] = $comment->getId();
    }

    return $comments;
  }

  private function messageKey(UserView $actor, string $content): string {
    return $this->key($actor->getUuid(), CommentContent::fromString($content)->toString());
  }

  private function key(string $userId, string $content): string {
    return $userId . ' ' . $content;
  }
}
