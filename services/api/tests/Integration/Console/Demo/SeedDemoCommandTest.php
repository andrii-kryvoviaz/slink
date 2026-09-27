<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Demo;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Image\Application\Command\TagImage\TagImageCommand;
use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Notification\Domain\Filter\NotificationListFilter;
use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\Integration\Http\HttpTestCase;
use UI\Console\Command\Demo\DemoAccountSeeder;
use UI\Console\Command\Demo\DemoActivitySeeder;
use UI\Console\Command\Demo\DemoCollectionSeeder;
use UI\Console\Command\Demo\DemoImageSeeder;
use UI\Console\Command\Demo\DemoSession;
use UI\Console\Command\Demo\DemoShareSeeder;
use UI\Console\Command\Demo\DemoSsoSeeder;
use UI\Console\Command\Demo\DemoTagSeeder;
use UI\Console\Command\Demo\SeedDemoCommand;

final class SeedDemoCommandTest extends HttpTestCase {
  private const string DEMO_USERNAME = 'demo';
  private const string DANIEL_ROE = 'daniel-roe-lpjb_UMOyx8-unsplash.jpg';
  private const string LUCA_BRAVO = 'luca-bravo-WeFDiEDModQ-unsplash.jpg';
  private const string BIEL_MORRO = 'biel-morro-bsSIk3LV_NE-unsplash.jpg';
  private const string KAZUEND = 'kazuend-2KXEb_8G5vo-unsplash.jpg';
  private const string UNLISTED = 'unlisted-picture.jpg';
  private const int PREDEFINED_TAG_COUNT = 30;
  private const string ALICE_PICTURE = 'alice/alice-picture.png';

  private const array SOCIAL_PICTURES = [
    'aaron-lau-EOnlL3L3IgQ-unsplash.jpg',
    'christian-grab-AygJgCM_vto-unsplash.jpg',
    'clay-banks-Icfk7F4Ku0w-unsplash.jpg',
    'filip-zrnzevic-_EMkxLdko9k-unsplash.jpg',
    'george-pisarevsky-EsxDnpNYfPA-unsplash.jpg',
    'jei-lee-yRXuXvy4sQ4-unsplash.jpg',
    'jordan-steranka-lpddCskeg4A-unsplash.jpg',
    'malte-schmidt-enGr5YbjQKQ-unsplash.jpg',
    'nate-rayfield-_WR6tUIAJe8-unsplash.jpg',
    'nathan-dumlao-ciO5L8pin8A-unsplash.jpg',
    'nicolette-meade-RL3F99l0XYE-unsplash.jpg',
    'patrick-langwallner-wiFOaBrX_wc-unsplash.jpg',
    'piotr-pekala-t4cbHkeyXz4-unsplash.jpg',
    'saffu-A7RzCegedb4-unsplash.jpg',
    'samuel-ferrara-dKJXkKCF2D8-unsplash.jpg',
  ];

  private const array COMMENTS = [
    ['kazuend-2KXEb_8G5vo-unsplash.jpg', 'bob', 'How long was the exposure on this? The stars are pin sharp.', null],
    ['kazuend-2KXEb_8G5vo-unsplash.jpg', 'demo', '20 seconds at f/2.8, ISO 3200. Tripod on the jetty.', 'How long was the exposure on this? The stars are pin sharp.'],
    ['kazuend-2KXEb_8G5vo-unsplash.jpg', 'bob', 'Adding that to my notes, thanks!', '20 seconds at f/2.8, ISO 3200. Tripod on the jetty.'],
    ['kazuend-2KXEb_8G5vo-unsplash.jpg', 'carol', 'Kawaguchiko? I missed this view by one cloudy night.', null],
    ['biel-morro-bsSIk3LV_NE-unsplash.jpg', 'alice', 'The eyes! Is she always this suspicious?', null],
    ['biel-morro-bsSIk3LV_NE-unsplash.jpg', 'demo', "Only when there's food on the table.", 'The eyes! Is she always this suspicious?'],
    ['biel-morro-bsSIk3LV_NE-unsplash.jpg', 'alice', 'So always, then.', "Only when there's food on the table."],
    ['luca-bravo-WeFDiEDModQ-unsplash.jpg', 'carol', 'Lago di Braies? I was there in June and the water was exactly this colour.', null],
    ['luca-bravo-WeFDiEDModQ-unsplash.jpg', 'demo', 'Yes, early morning before the boats go out.', 'Lago di Braies? I was there in June and the water was exactly this colour.'],
    ['luca-bravo-WeFDiEDModQ-unsplash.jpg', 'carol', 'Worth the 5am alarm.', 'Yes, early morning before the boats go out.'],
    ['malte-schmidt-enGr5YbjQKQ-unsplash.jpg', 'carol', 'Manhattanhenge! Did you have to fight the crowd for this spot?', null],
    ['malte-schmidt-enGr5YbjQKQ-unsplash.jpg', 'demo', 'Half of Midtown was standing in the street with me.', 'Manhattanhenge! Did you have to fight the crowd for this spot?'],
    ['malte-schmidt-enGr5YbjQKQ-unsplash.jpg', 'alice', 'I was there too, two blocks down. Total chaos.', 'Half of Midtown was standing in the street with me.'],
    ['malte-schmidt-enGr5YbjQKQ-unsplash.jpg', 'carol', 'Worth it.', 'Half of Midtown was standing in the street with me.'],
    ['piotr-pekala-t4cbHkeyXz4-unsplash.jpg', 'bob', 'That light on the ridge. Moody in the best way.', null],
    ['piotr-pekala-t4cbHkeyXz4-unsplash.jpg', 'demo', 'Ten minutes later it was pouring.', 'That light on the ridge. Moody in the best way.'],
    ['piotr-pekala-t4cbHkeyXz4-unsplash.jpg', 'bob', 'The best light always comes right before the rain.', 'Ten minutes later it was pouring.'],
    ['nate-rayfield-_WR6tUIAJe8-unsplash.jpg', 'bob', 'Is that the core of the Milky Way? What month was this?', null],
    ['nate-rayfield-_WR6tUIAJe8-unsplash.jpg', 'demo', 'Late May, around 2am.', 'Is that the core of the Milky Way? What month was this?'],
    ['nate-rayfield-_WR6tUIAJe8-unsplash.jpg', 'bob', 'Circling May in my calendar.', 'Late May, around 2am.'],
    ['clay-banks-Icfk7F4Ku0w-unsplash.jpg', 'bob', 'Which rooftop is this? The view is unreal.', null],
    ['clay-banks-Icfk7F4Ku0w-unsplash.jpg', 'demo', "A friend's office, I promised not to share the address.", 'Which rooftop is this? The view is unreal.'],
    ['clay-banks-Icfk7F4Ku0w-unsplash.jpg', 'bob', 'Fair enough!', "A friend's office, I promised not to share the address."],
    ['daniel-roe-lpjb_UMOyx8-unsplash.jpg', 'carol', 'Lake Louise before the crowds. How early did you get there?', null],
    ['daniel-roe-lpjb_UMOyx8-unsplash.jpg', 'demo', 'Sunrise, and it was already busy.', 'Lake Louise before the crowds. How early did you get there?'],
    ['daniel-roe-lpjb_UMOyx8-unsplash.jpg', 'carol', 'Still looks peaceful here.', 'Sunrise, and it was already busy.'],
    ['patrick-langwallner-wiFOaBrX_wc-unsplash.jpg', 'carol', 'Drone? The texture of the water is amazing.', null],
    ['patrick-langwallner-wiFOaBrX_wc-unsplash.jpg', 'demo', 'Yes, from about 80 metres up.', 'Drone? The texture of the water is amazing.'],
    ['patrick-langwallner-wiFOaBrX_wc-unsplash.jpg', 'carol', 'It looks like a painting.', 'Yes, from about 80 metres up.'],
    ['aaron-lau-EOnlL3L3IgQ-unsplash.jpg', 'alice', 'Love the low angle, the road lines pull you straight into the bridge.', null],
    ['aaron-lau-EOnlL3L3IgQ-unsplash.jpg', 'carol', 'Same thought. Feels like lying on the road.', 'Love the low angle, the road lines pull you straight into the bridge.'],
    ['nathan-dumlao-ciO5L8pin8A-unsplash.jpg', 'alice', 'Black and white really suits this one.', null],
    ['jei-lee-yRXuXvy4sQ4-unsplash.jpg', 'alice', 'Instant spring. Needed this today.', null],
    ['nicolette-meade-RL3F99l0XYE-unsplash.jpg', 'carol', 'This looks like a page from a pressed flower album.', null],
    ['saffu-A7RzCegedb4-unsplash.jpg', 'alice', 'So calm. Would love this as a print.', null],
    ['samuel-ferrara-dKJXkKCF2D8-unsplash.jpg', 'bob', 'Those layers of blue ridges are my favourite kind of view.', null],
    ['george-pisarevsky-EsxDnpNYfPA-unsplash.jpg', 'bob', 'Crabapple? The backlight makes it glow.', null],
  ];

  private string $picturesPath;

  /** @var array<string, string> */
  private array $pictures = [];

  private string $alicePicture;

  private mixed $previousDemoMode = null;

  protected function setUp(): void {
    $this->previousDemoMode = $_ENV['DEMO_MODE_ENABLED'] ?? null;
    $this->setDemoMode('true');

    parent::setUp();

    $this->picturesPath = \sys_get_temp_dir() . '/slink-demo-seed-' . \bin2hex(\random_bytes(6));
    \mkdir($this->picturesPath);

    foreach ([self::DANIEL_ROE, self::LUCA_BRAVO, self::BIEL_MORRO, self::KAZUEND, self::UNLISTED] as $fileName) {
      $this->pictures[$fileName] = $this->randomPng();
    }

    \mkdir(\dirname($this->picturesPath . '/' . self::ALICE_PICTURE));
    $this->alicePicture = $this->randomPng();
  }

  protected function tearDown(): void {
    foreach (\array_keys($this->pictures) as $fileName) {
      @\unlink($this->picturesPath . '/' . $fileName);
    }

    @\unlink($this->picturesPath . '/' . self::ALICE_PICTURE);
    @\rmdir(\dirname($this->picturesPath . '/' . self::ALICE_PICTURE));
    @\rmdir($this->picturesPath);

    parent::tearDown();

    $this->setDemoMode($this->previousDemoMode);
  }

  #[Test]
  public function itFailsWhenDemoUserDoesNotExist(): void {
    $tester = $this->runSeed();

    self::assertSame(Command::FAILURE, $tester->getStatusCode());
    self::assertStringContainsString('Demo user', $tester->getDisplay());
  }

  #[Test]
  public function itSeedsEverythingOnAnEmptyInstance(): void {
    $userId = $this->bootDemoUser();

    $tester = $this->runSeed();

    self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
    self::assertNull($this->tokenStorage()->getToken());

    self::assertCount(self::PREDEFINED_TAG_COUNT, $this->tags()->findByUserId($userId));
    self::assertCount(\count($this->pictures), $this->images()->findByUserId($userId));

    self::assertSame(
      ['#Nature/Lake', '#Nature/Mountain', '#Travel/Landscape'],
      $this->tagPathsOf(self::DANIEL_ROE, $userId),
    );
    self::assertSame(
      ['#Nature/Lake', '#Nature/Mountain', '#Nature/Night Sky'],
      $this->tagPathsOf(self::KAZUEND, $userId),
    );
    self::assertSame([], $this->tagPathsOf(self::UNLISTED, $userId));

    self::assertSame(
      [self::DANIEL_ROE, self::LUCA_BRAVO, self::KAZUEND],
      $this->collectionFileNames('High Places', $userId),
    );
    self::assertSame([self::KAZUEND], $this->collectionFileNames('After Dark', $userId));
    self::assertSame([], $this->collectionFileNames('In Bloom', $userId));
    self::assertSame([], $this->collectionFileNames('Forest Quiet', $userId));

    foreach (\array_keys($this->pictures) as $fileName) {
      self::assertTrue($this->imageShareOf($fileName, $userId)->isPublished(), $fileName);
    }

    self::assertNotNull($this->imageShareOf(self::BIEL_MORRO, $userId)->getPassword());
    self::assertNull($this->imageShareOf(self::DANIEL_ROE, $userId)->getPassword());

    $now = Clock::get()->now();
    $danielExpiry = $this->imageShareOf(self::DANIEL_ROE, $userId)->getExpiresAt();
    self::assertNotNull($danielExpiry);
    self::assertGreaterThan($now->modify('+29 days'), $danielExpiry);
    self::assertLessThan($now->modify('+31 days'), $danielExpiry);

    $lucaExpiry = $this->imageShareOf(self::LUCA_BRAVO, $userId)->getExpiresAt();
    self::assertNotNull($lucaExpiry);
    self::assertLessThan($now, $lucaExpiry);

    self::assertTrue($this->collectionShareOf('High Places', $userId)->isPublished());
    self::assertTrue($this->collectionShareOf('After Dark', $userId)->isPublished());
    self::assertNull($this->collectionShareOrNull('In Bloom', $userId));

    foreach (['authentik', 'keycloak'] as $slug) {
      $provider = $this->providers()->findByProvider(ProviderSlug::fromString($slug));
      self::assertNotNull($provider, $slug);
      self::assertFalse($provider->isEnabled());
    }
  }

  #[Test]
  public function itCreatesNothingNewOnSecondRun(): void {
    $userId = $this->bootDemoUser();

    self::assertSame(Command::SUCCESS, $this->runSeed()->getStatusCode());
    $before = $this->rowCounts();

    $tester = $this->runSeed();

    self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
    self::assertSame($before, $this->rowCounts());
    self::assertSame(['High Places'], $this->collections()->findNamesByPatternAndUser('High Places', $userId->toString()));
  }

  #[Test]
  public function itReusesAnImageAlreadyUploadedWithTheSameBytes(): void {
    $userId = $this->bootDemoUser();
    $existingId = $this->uploadAsDemoUser(self::DANIEL_ROE, $userId);

    $tester = $this->runSeed();

    self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
    self::assertCount(\count($this->pictures), $this->images()->findByUserId($userId));

    $image = $this->imageOf(self::DANIEL_ROE, $userId);
    self::assertSame($existingId, $image->getUuid());
    self::assertSame(
      ['#Nature/Lake', '#Nature/Mountain', '#Travel/Landscape'],
      $this->tagPathsOf(self::DANIEL_ROE, $userId),
    );
    self::assertContains(self::DANIEL_ROE, $this->collectionFileNames('High Places', $userId));
    self::assertTrue($this->imageShareOf(self::DANIEL_ROE, $userId)->isPublished());
  }

  #[Test]
  public function itRemovesUnmappedTagsFromMappedImagesOnly(): void {
    $userId = $this->bootDemoUser();
    self::assertSame(Command::SUCCESS, $this->runSeed()->getStatusCode());

    $this->tagAsDemoUser(self::DANIEL_ROE, '#People/Group', $userId);
    $this->tagAsDemoUser(self::UNLISTED, '#People/Group', $userId);

    $tester = $this->runSeed();

    self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
    self::assertStringContainsString(\sprintf('%s untagged #People/Group', self::DANIEL_ROE), $tester->getDisplay());
    self::assertSame(
      ['#Nature/Lake', '#Nature/Mountain', '#Travel/Landscape'],
      $this->tagPathsOf(self::DANIEL_ROE, $userId),
    );
    self::assertSame(['#People/Group'], $this->tagPathsOf(self::UNLISTED, $userId));
    self::assertCount(self::PREDEFINED_TAG_COUNT, $this->tags()->findByUserId($userId));

    $before = $this->rowCounts();
    $tester = $this->runSeed();

    self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
    self::assertStringContainsString('Image tags: 0 created', $tester->getDisplay());
    self::assertSame($before, $this->rowCounts());
  }

  #[Test]
  public function itSeedsAccountsWithAFullPageOfNotifications(): void {
    $userId = $this->bootDemoUser();
    $this->addSocialPictures();

    $tester = $this->runSeed();

    self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
    self::assertNull($this->tokenStorage()->getToken());

    foreach (['alice' => 'Alice', 'bob' => 'Bob', 'carol' => 'Carol'] as $username => $displayName) {
      $account = $this->users()->oneByUsername(Username::fromString($username));
      self::assertSame($displayName, $account->getDisplayName());
      self::assertSame($username . '@example.com', $account->getEmail());
      self::assertSame(UserStatus::Active->value, $account->getStatus());
      self::assertSame(['ROLE_USER'], $this->userStore()->get(ID::fromString($account->getUuid()))->getRoles());
    }

    $notifications = $this->notificationsOf($userId);
    self::assertCount(50, $notifications);
    self::assertSame(
      ['added_to_bookmarks' => 22, 'comment' => 18, 'comment_reply' => 10],
      $this->countBy($notifications, static fn(NotificationView $notification): string => $notification->getTypeValue()),
    );
    self::assertSame(8, $this->notificationRepository()->countUnreadByUserId($userId->toString()));
    self::assertCount(42, \array_filter($notifications, static fn(NotificationView $notification): bool => $notification->isRead()));
  }

  #[Test]
  public function itSpreadsNotificationsOverThreeWeeks(): void {
    $userId = $this->bootDemoUser();
    $this->addSocialPictures();

    self::assertSame(Command::SUCCESS, $this->runSeed()->getStatusCode());

    $now = Clock::get()->now();
    $recentCutoff = $now->modify('-48 hours');
    $notifications = $this->notificationsOf($userId);
    $createdAt = \array_map(static fn(NotificationView $notification): int => $notification->getCreatedAt()->getTimestamp(), $notifications);
    \sort($createdAt);

    self::assertGreaterThan($now->modify('-21 days')->getTimestamp(), \array_first($createdAt));
    self::assertLessThan($now->modify('-18 days')->getTimestamp(), \array_first($createdAt));
    self::assertLessThan($now->getTimestamp(), \array_last($createdAt));

    foreach ($notifications as $notification) {
      $isRecent = $notification->getCreatedAt()->getTimestamp() > $recentCutoff->getTimestamp();
      self::assertSame(!$isRecent, $notification->isRead(), $notification->getId());
    }

    self::assertCount(8, \array_filter($createdAt, static fn(int $timestamp): bool => $timestamp > $recentCutoff->getTimestamp()));
  }

  #[Test]
  public function itUploadsAccountPicturesAndBookmarksThemForTheDemoUser(): void {
    $userId = $this->bootDemoUser();

    $tester = $this->runSeed();

    self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
    self::assertCount(\count($this->pictures), $this->images()->findByUserId($userId));

    $alice = $this->users()->oneByUsername(Username::fromString('alice'));
    $aliceImage = $this->images()->findBySha1Hash(\sha1($this->alicePicture), ID::fromString($alice->getUuid()));
    self::assertNotNull($aliceImage);
    self::assertTrue($aliceImage->getAttributes()->isPublic());
    self::assertTrue($this->bookmarks()->isBookmarkedByUser($aliceImage->getUuid(), $userId->toString()));
  }

  #[Test]
  public function itCreatesEveryCommentThreadWithItsReplyParents(): void {
    $userId = $this->bootDemoUser();
    $this->addSocialPictures();

    self::assertSame(Command::SUCCESS, $this->runSeed()->getStatusCode());

    $actual = [];
    foreach (\array_unique(\array_column(self::COMMENTS, 0)) as $fileName) {
      foreach ($this->commentRepository()->findByImageId($this->imageOf($fileName, $userId)->getUuid(), 1, 100) as $comment) {
        $actual[] = $this->describeComment($fileName, $comment);
        $parent = $comment->getReferencedComment();

        if ($parent !== null) {
          self::assertGreaterThan($parent->getCreatedAt(), $comment->getCreatedAt(), $comment->getContent());
        }
      }
    }

    $expected = self::COMMENTS;
    \sort($expected);
    \sort($actual);
    self::assertSame($expected, $actual);
  }

  #[Test]
  public function itCreatesNoActivityOnSecondRun(): void {
    $userId = $this->bootDemoUser();
    $this->addSocialPictures();

    self::assertSame(Command::SUCCESS, $this->runSeed()->getStatusCode());
    $before = $this->rowCounts();
    $unreadBefore = $this->notificationRepository()->countUnreadByUserId($userId->toString());

    $tester = $this->runSeed();

    self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());
    self::assertSame($before, $this->rowCounts());
    self::assertSame(8, $unreadBefore);
    self::assertSame($unreadBefore, $this->notificationRepository()->countUnreadByUserId($userId->toString()));
    self::assertStringContainsString('Notifications: 0 created', $tester->getDisplay());
  }

  private function bootDemoUser(): ID {
    return ID::fromString($this->createUser('demo@local.test', self::DEMO_USERNAME, self::PASSWORD));
  }

  private function addSocialPictures(): void {
    foreach (self::SOCIAL_PICTURES as $fileName) {
      $this->pictures[$fileName] = $this->randomPng();
    }
  }

  /**
   * @return array{string, string, ?string, ?string}
   */
  private function describeComment(string $fileName, CommentView $comment): array {
    return [
      $fileName,
      (string) $comment->getUser()?->getUsername(),
      $this->decode($comment->getContent()),
      $this->decode($comment->getReferencedComment()?->getContent()),
    ];
  }

  private function decode(?string $content): ?string {
    if ($content === null) {
      return null;
    }

    return \html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
  }

  /**
   * @return list<NotificationView>
   */
  private function notificationsOf(ID $userId): array {
    $notifications = $this->notificationRepository()->findByUserId($userId->toString(), new NotificationListFilter(), 1, 200);

    return \iterator_to_array($notifications->getIterator(), false);
  }

  /**
   * @param list<NotificationView> $notifications
   * @param \Closure(NotificationView): string $key
   * @return array<string, int>
   */
  private function countBy(array $notifications, \Closure $key): array {
    $counts = \array_count_values(\array_map($key, $notifications));
    \ksort($counts);

    return $counts;
  }

  private function setDemoMode(mixed $value): void {
    if ($value === null) {
      unset($_ENV['DEMO_MODE_ENABLED'], $_SERVER['DEMO_MODE_ENABLED']);

      return;
    }

    $_ENV['DEMO_MODE_ENABLED'] = $value;
    $_SERVER['DEMO_MODE_ENABLED'] = $value;
  }

  private function runSeed(): CommandTester {
    foreach ($this->pictures as $fileName => $bytes) {
      \file_put_contents($this->picturesPath . '/' . $fileName, $bytes);
    }

    \file_put_contents($this->picturesPath . '/' . self::ALICE_PICTURE, $this->alicePicture);

    $container = static::getContainer();

    /** @var ConfigurationProviderInterface<mixed> $configurationProvider */
    $configurationProvider = $container->get(ConfigurationProviderInterface::class);
    /** @var DemoTagSeeder $tagSeeder */
    $tagSeeder = $container->get(DemoTagSeeder::class);
    /** @var DemoImageSeeder $imageSeeder */
    $imageSeeder = $container->get(DemoImageSeeder::class);
    /** @var DemoCollectionSeeder $collectionSeeder */
    $collectionSeeder = $container->get(DemoCollectionSeeder::class);
    /** @var DemoShareSeeder $shareSeeder */
    $shareSeeder = $container->get(DemoShareSeeder::class);
    /** @var DemoSsoSeeder $ssoSeeder */
    $ssoSeeder = $container->get(DemoSsoSeeder::class);
    /** @var DemoSession $session */
    $session = $container->get(DemoSession::class);
    /** @var DemoAccountSeeder $accountSeeder */
    $accountSeeder = $container->get(DemoAccountSeeder::class);
    /** @var DemoActivitySeeder $activitySeeder */
    $activitySeeder = $container->get(DemoActivitySeeder::class);

    $command = new SeedDemoCommand(
      $configurationProvider,
      $session,
      $tagSeeder,
      $imageSeeder,
      $collectionSeeder,
      $shareSeeder,
      $ssoSeeder,
      $accountSeeder,
      $activitySeeder,
      $this->picturesPath,
    );
    (new Application())->addCommand($command);

    $tester = new CommandTester($command);
    $tester->execute([]);

    $this->entityManager()->clear();

    return $tester;
  }

  private function uploadAsDemoUser(string $fileName, ID $userId): string {
    $path = $this->picturesPath . '/' . $fileName;
    \file_put_contents($path, $this->pictures[$fileName]);

    $command = new UploadImageCommand(new File($path), true);
    $this->commandBus()->handle($command->withContext(['userId' => $userId->toString()]));

    return $command->getId()->toString();
  }

  private function tagAsDemoUser(string $fileName, string $tagPath, ID $userId): void {
    $tag = $this->tags()->findByPathAndUser($tagPath, $userId);
    self::assertNotNull($tag, $tagPath);

    /** @var non-empty-string $uuid */
    $uuid = $userId->toString();
    $user = JwtUser::createFromPayload($uuid, ['roles' => ['ROLE_USER']]);
    $this->tokenStorage()->setToken(new UsernamePasswordToken($user, 'api', $user->getRoles()));

    try {
      $command = new TagImageCommand($this->imageOf($fileName, $userId)->getUuid(), $tag->getUuid());
      $this->commandBus()->handle($command->withContext(['userId' => $userId->toString()]));
    } finally {
      $this->tokenStorage()->setToken(null);
    }

    $this->entityManager()->clear();
  }

  private function imageOf(string $fileName, ID $userId): ImageView {
    $image = $this->images()->findBySha1Hash(\sha1($this->pictures[$fileName]), $userId);
    self::assertNotNull($image, $fileName);

    return $image;
  }

  /**
   * @return list<string>
   */
  private function tagPathsOf(string $fileName, ID $userId): array {
    $imageId = ID::fromString($this->imageOf($fileName, $userId)->getUuid());

    return \array_map(
      static fn(TagView $tag): string => $tag->getPath(),
      \array_values($this->tags()->findByImageId($imageId)),
    );
  }

  /**
   * @return list<string>
   */
  private function collectionFileNames(string $name, ID $userId): array {
    $fileNamesById = [];
    foreach (\array_keys($this->pictures) as $fileName) {
      $fileNamesById[$this->imageOf($fileName, $userId)->getUuid()] = $fileName;
    }

    $fileNames = [];
    foreach ($this->collectionItems()->getByCollectionIdSorted($this->collectionIdOf($name, $userId)) as $item) {
      $fileNames[] = $fileNamesById[$item->getItemId()];
    }

    return $fileNames;
  }

  private function collectionIdOf(string $name, ID $userId): string {
    foreach ($this->collections()->findByIds(\array_values($this->collections()->findIdsByUserId($userId->toString()))) as $collection) {
      if ($collection->getName() === $name) {
        return $collection->getUuid();
      }
    }

    self::fail(\sprintf('Collection "%s" not found', $name));
  }

  private function imageShareOf(string $fileName, ID $userId): ShareView {
    $shares = $this->shares()->findAllByShareable($this->imageOf($fileName, $userId)->getUuid(), ShareableType::Image);
    self::assertCount(1, $shares, $fileName);

    return $shares[0];
  }

  private function collectionShareOf(string $name, ID $userId): ShareView {
    $share = $this->collectionShareOrNull($name, $userId);
    self::assertNotNull($share, $name);

    return $share;
  }

  private function collectionShareOrNull(string $name, ID $userId): ?ShareView {
    return $this->shares()->findByShareable($this->collectionIdOf($name, $userId), ShareableType::Collection);
  }

  /**
   * @return array<string, int>
   */
  private function rowCounts(): array {
    $connection = $this->entityManager()->getConnection();
    $counts = [];

    foreach (['image', 'tag', 'image_to_tag', 'collection', 'collection_item', 'share', 'short_url', 'oauth_provider', 'user', 'bookmark', 'comment', 'notification'] as $table) {
      $counts[$table] = (int) $connection->fetchOne(\sprintf('SELECT COUNT(*) FROM "%s"', $table));
    }

    return $counts;
  }

  private function randomPng(): string {
    $ihdr = \pack('NNCCCCC', 4, 4, 8, 2, 0, 0, 0);

    $raw = '';
    for ($y = 0; $y < 4; $y++) {
      $raw .= "\x00" . \random_bytes(12);
    }

    $idat = \gzcompress($raw);
    self::assertNotFalse($idat);

    return "\x89PNG\r\n\x1a\n"
      . $this->pngChunk('IHDR', $ihdr)
      . $this->pngChunk('IDAT', $idat)
      . $this->pngChunk('IEND', '');
  }

  private function pngChunk(string $type, string $data): string {
    return \pack('N', \strlen($data)) . $type . $data . \pack('N', \crc32($type . $data));
  }

  private function tokenStorage(): TokenStorageInterface {
    /** @var TokenStorageInterface $tokenStorage */
    $tokenStorage = static::getContainer()->get('security.token_storage');

    return $tokenStorage;
  }

  private function entityManager(): EntityManagerInterface {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager;
  }

  private function images(): ImageRepositoryInterface {
    /** @var ImageRepositoryInterface $repository */
    $repository = static::getContainer()->get(ImageRepositoryInterface::class);

    return $repository;
  }

  private function tags(): TagRepositoryInterface {
    /** @var TagRepositoryInterface $repository */
    $repository = static::getContainer()->get(TagRepositoryInterface::class);

    return $repository;
  }

  private function collections(): CollectionRepositoryInterface {
    /** @var CollectionRepositoryInterface $repository */
    $repository = static::getContainer()->get(CollectionRepositoryInterface::class);

    return $repository;
  }

  private function collectionItems(): CollectionItemRepositoryInterface {
    /** @var CollectionItemRepositoryInterface $repository */
    $repository = static::getContainer()->get(CollectionItemRepositoryInterface::class);

    return $repository;
  }

  private function shares(): ShareRepositoryInterface {
    /** @var ShareRepositoryInterface $repository */
    $repository = static::getContainer()->get(ShareRepositoryInterface::class);

    return $repository;
  }

  private function users(): UserRepositoryInterface {
    /** @var UserRepositoryInterface $repository */
    $repository = static::getContainer()->get(UserRepositoryInterface::class);

    return $repository;
  }

  private function userStore(): UserStoreRepositoryInterface {
    /** @var UserStoreRepositoryInterface $repository */
    $repository = static::getContainer()->get(UserStoreRepositoryInterface::class);

    return $repository;
  }

  private function bookmarks(): BookmarkRepositoryInterface {
    /** @var BookmarkRepositoryInterface $repository */
    $repository = static::getContainer()->get(BookmarkRepositoryInterface::class);

    return $repository;
  }

  private function commentRepository(): CommentRepositoryInterface {
    /** @var CommentRepositoryInterface $repository */
    $repository = static::getContainer()->get(CommentRepositoryInterface::class);

    return $repository;
  }

  private function notificationRepository(): NotificationRepositoryInterface {
    /** @var NotificationRepositoryInterface $repository */
    $repository = static::getContainer()->get(NotificationRepositoryInterface::class);

    return $repository;
  }

  private function providers(): OAuthProviderRepositoryInterface {
    /** @var OAuthProviderRepositoryInterface $repository */
    $repository = static::getContainer()->get(OAuthProviderRepositoryInterface::class);

    return $repository;
  }
}
