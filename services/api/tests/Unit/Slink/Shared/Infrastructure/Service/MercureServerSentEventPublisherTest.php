<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\Service;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\Service\MercureServerSentEventPublisher;
use Symfony\Component\Mercure\Jwt\StaticTokenProvider;
use Symfony\Component\Mercure\MockHub;
use Symfony\Component\Mercure\Update;

#[Group('sc-888')]
final class MercureServerSentEventPublisherTest extends TestCase {
  /** @var list<Update> */
  private array $updates = [];

  private MercureServerSentEventPublisher $publisher;

  protected function setUp(): void {
    $this->updates = [];

    $hub = new MockHub(
      'https://mercure.slink.example/.well-known/mercure',
      new StaticTokenProvider('token'),
      function (Update $update): string {
        $this->updates[] = $update;

        return 'update-id';
      },
    );

    $this->publisher = new MercureServerSentEventPublisher($hub);
  }

  #[Test]
  public function itPublishesPrivateUpdateWithTopicAndJsonData(): void {
    $data = ['event' => 'comment_created', 'comment' => ['id' => 'c1']];

    $this->publisher->publish('comments/image/abc', $data, true);

    $this->assertCount(1, $this->updates);
    $update = $this->updates[0];

    $this->assertSame(['comments/image/abc'], $update->getTopics());
    $this->assertSame($data, json_decode($update->getData(), true, 512, JSON_THROW_ON_ERROR));
    $this->assertTrue($update->isPrivate());
  }

  #[Test]
  public function itPublishesPublicUpdateByDefault(): void {
    $this->publisher->publish('public-feed', ['event' => 'image_uploaded']);

    $this->assertCount(1, $this->updates);
    $this->assertFalse($this->updates[0]->isPrivate());
  }
}
