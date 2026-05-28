<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Application\Command\CreateShare;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Share\Application\Command\CreateShare\CreateShareCommand;
use Slink\Share\Application\Command\CreateShare\CreateShareHandler;
use Slink\Share\Domain\Event\ShortUrlWasRegenerated;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Share\Domain\ValueObject\ShareParams;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class CreateShareHandlerTest extends TestCase {
  private ShareStoreRepositoryInterface&MockObject $shareStore;
  private ShareServiceInterface $shareService;
  private CreateShareHandler $handler;

  protected function setUp(): void {
    parent::setUp();

    $this->shareStore = $this->createMock(ShareStoreRepositoryInterface::class);
    $this->shareService = $this->createStub(ShareServiceInterface::class);
    $this->handler = new CreateShareHandler($this->shareStore, $this->shareService);
  }

  #[Test]
  public function itCreatesNewShareWhenNoneExists(): void {
    $shareable = ShareableReference::forImage(ID::generate());
    $targetPath = TargetPath::fromString('/image/file.jpg');
    $context = ShareContext::for($shareable)->withShortUrl(ID::generate(), 'NEWCODE12');

    $this->shareService->method('buildContext')->willReturn($context);
    $this->shareStore->method('findByTargetPath')->willReturn(null);
    $this->shareStore->expects($this->once())->method('store');

    $result = ($this->handler)(new CreateShareCommand(ShareParams::withTargetPath($shareable, $targetPath)));

    $this->assertTrue($result->wasCreated());
  }

  #[Test]
  public function itRegeneratesShortUrlWhenExistingShareHasStaleCodeAndIsUnpublished(): void {
    $shareable = ShareableReference::forImage(ID::generate());
    $targetPath = TargetPath::fromString('/image/file.jpg');
    $candidateLong = 'NEWLONGERCODE';

    $share = $this->existingShareWithShortCode($shareable, $targetPath, 'OLDSHORT');
    $context = ShareContext::for($shareable)->withShortUrl(ID::generate(), $candidateLong);

    $this->shareService->method('buildContext')->willReturn($context);
    $this->shareStore->method('findByTargetPath')->willReturn($share);
    $this->shareStore->expects($this->once())->method('store')->with($share);

    $result = ($this->handler)(new CreateShareCommand(ShareParams::withTargetPath($shareable, $targetPath)));

    $events = $share->releaseEvents();
    $regenEvents = array_values(array_filter($events, fn($event): bool => $event instanceof ShortUrlWasRegenerated));

    $this->assertFalse($result->wasCreated());
    $this->assertCount(1, $regenEvents);
    $this->assertSame($candidateLong, $regenEvents[0]->shortCode);
    $this->assertSame($candidateLong, $share->getShortCode());
  }

  #[Test]
  public function itDoesNotRegenerateShortUrlWhenExistingShareIsPublished(): void {
    $shareable = ShareableReference::forImage(ID::generate());
    $targetPath = TargetPath::fromString('/image/file.jpg');

    $share = $this->existingShareWithShortCode($shareable, $targetPath, 'OLDSHORT');
    $share->publish();
    $share->releaseEvents();

    $context = ShareContext::for($shareable)->withShortUrl(ID::generate(), 'NEWLONGERCODE');

    $this->shareService->method('buildContext')->willReturn($context);
    $this->shareStore->method('findByTargetPath')->willReturn($share);
    $this->shareStore->expects($this->once())->method('store')->with($share);

    $result = ($this->handler)(new CreateShareCommand(ShareParams::withTargetPath($shareable, $targetPath)));

    $events = $share->releaseEvents();
    $regenEvents = array_filter($events, fn($event): bool => $event instanceof ShortUrlWasRegenerated);

    $this->assertFalse($result->wasCreated());
    $this->assertCount(0, $regenEvents);
    $this->assertSame('OLDSHORT', $share->getShortCode());
  }

  #[Test]
  public function itDoesNotRegenerateShortUrlWhenExistingCodeAlreadyMatchesConfiguredLength(): void {
    $shareable = ShareableReference::forImage(ID::generate());
    $targetPath = TargetPath::fromString('/image/file.jpg');

    $share = $this->existingShareWithShortCode($shareable, $targetPath, 'CODE0008');
    $context = ShareContext::for($shareable)->withShortUrl(ID::generate(), 'NEWCODE9');

    $this->shareService->method('buildContext')->willReturn($context);
    $this->shareStore->method('findByTargetPath')->willReturn($share);
    $this->shareStore->expects($this->once())->method('store')->with($share);

    ($this->handler)(new CreateShareCommand(ShareParams::withTargetPath($shareable, $targetPath)));

    $events = $share->releaseEvents();
    $regenEvents = array_filter($events, fn($event): bool => $event instanceof ShortUrlWasRegenerated);

    $this->assertCount(0, $regenEvents);
    $this->assertSame('CODE0008', $share->getShortCode());
  }

  #[Test]
  public function itDoesNotRegenerateWhenUrlShorteningIsDisabledOnContext(): void {
    $shareable = ShareableReference::forImage(ID::generate());
    $targetPath = TargetPath::fromString('/image/file.jpg');

    $share = $this->existingShareWithShortCode($shareable, $targetPath, 'OLDSHORT');
    $contextWithoutShortUrl = ShareContext::for($shareable);

    $this->shareService->method('buildContext')->willReturn($contextWithoutShortUrl);
    $this->shareStore->method('findByTargetPath')->willReturn($share);
    $this->shareStore->expects($this->once())->method('store')->with($share);

    ($this->handler)(new CreateShareCommand(ShareParams::withTargetPath($shareable, $targetPath)));

    $events = $share->releaseEvents();
    $regenEvents = array_filter($events, fn($event): bool => $event instanceof ShortUrlWasRegenerated);

    $this->assertCount(0, $regenEvents);
    $this->assertSame('OLDSHORT', $share->getShortCode());
  }

  private function existingShareWithShortCode(ShareableReference $shareable, TargetPath $targetPath, string $shortCode): Share {
    $share = Share::create(
      ID::generate(),
      $shareable,
      $targetPath,
      DateTime::now(),
      ShareContext::for($shareable),
    );
    $share->addShortUrl(ID::generate(), $shortCode);

    /* sanity check: share was hydrated with the expected short code */
    if ($share->getShortCode() !== $shortCode) {
      throw new \LogicException('Test setup error: short code not applied');
    }

    /* drop ShareWasCreated + ShortUrlWasAdded so test only observes handler-recorded events */
    $share->releaseEvents();

    return $share;
  }
}
