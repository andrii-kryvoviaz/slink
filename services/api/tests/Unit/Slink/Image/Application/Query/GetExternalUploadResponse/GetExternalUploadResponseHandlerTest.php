<?php

declare(strict_types=1);

namespace Unit\Slink\Image\Application\Query\GetExternalUploadResponse;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Slink\Image\Application\Query\GetExternalUploadResponse\GetExternalUploadResponseHandler;
use Slink\Image\Application\Query\GetExternalUploadResponse\GetExternalUploadResponseQuery;
use Slink\Share\Application\Command\CreateShare\CreateShareCommand;
use Slink\Share\Application\Command\CreateShare\CreateShareResult;
use Slink\Share\Application\Command\PublishShare\PublishShareCommand;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\TargetPath;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\UserPreferencesRepositoryInterface;
use Slink\User\Domain\ValueObject\UserPreferences;
use Slink\User\Infrastructure\ReadModel\View\UserPreferencesView;

final class GetExternalUploadResponseHandlerTest extends TestCase {
  private CommandBusInterface&MockObject $commandBus;
  private ShareUrlBuilderInterface&Stub $shareUrlBuilder;
  private ShareServiceInterface&MockObject $shareService;
  private UserPreferencesRepositoryInterface&Stub $preferencesRepository;

  protected function setUp(): void {
    parent::setUp();

    $this->commandBus = $this->createMock(CommandBusInterface::class);
    $this->shareUrlBuilder = $this->createStub(ShareUrlBuilderInterface::class);
    $this->shareService = $this->createMock(ShareServiceInterface::class);
    $this->preferencesRepository = $this->createStub(UserPreferencesRepositoryInterface::class);

    $this->shareUrlBuilder->method('buildTargetPath')->willReturn(TargetPath::fromString('/image/test.jpg'));
  }

  private function createHandler(): GetExternalUploadResponseHandler {
    return new GetExternalUploadResponseHandler(
      $this->commandBus,
      $this->shareUrlBuilder,
      $this->shareService,
      $this->preferencesRepository,
    );
  }

  private function createPreferencesView(bool $autoPublish): UserPreferencesView {
    $view = $this->createStub(UserPreferencesView::class);
    $view->method('getPreferences')->willReturn(
      UserPreferences::create(externalUploadAutoPublish: $autoPublish),
    );

    return $view;
  }

  private function createShareStub(string $shareId, bool $isPublished): Share {
    $share = $this->createStub(Share::class);
    $share->method('aggregateRootId')->willReturn(ID::fromString($shareId));
    $share->method('isPublished')->willReturn($isPublished);

    return $share;
  }

  #[Test]
  public function itPublishesBothSharesWhenPreferenceIsTrue(): void {
    $imageId = Uuid::uuid4()->toString();
    $userId = Uuid::uuid4()->toString();
    $mainShareId = Uuid::uuid4()->toString();
    $thumbShareId = Uuid::uuid4()->toString();
    $mainUrl = '/share/main';
    $thumbUrl = '/share/thumb';

    $this->preferencesRepository = $this->createStub(UserPreferencesRepositoryInterface::class);
    $this->preferencesRepository->method('findByUserId')->willReturn($this->createPreferencesView(true));

    $mainShare = $this->createShareStub($mainShareId, false);
    $thumbShare = $this->createShareStub($thumbShareId, false);

    $invocations = [];
    $this->commandBus
      ->expects($this->exactly(4))
      ->method('handleSync')
      ->willReturnCallback(function (object $command) use (&$invocations, $mainShare, $thumbShare): mixed {
        $invocations[] = $command;
        $createCount = count(array_filter($invocations, static fn($c) => $c instanceof CreateShareCommand));

        if ($command instanceof CreateShareCommand) {
          if ($createCount === 1) {
            return CreateShareResult::created($mainShare);
          }
          return CreateShareResult::created($thumbShare);
        }

        return null;
      });

    $this->shareService
      ->expects($this->exactly(2))
      ->method('resolveUrl')
      ->willReturnCallback(function (Share $share, bool $isAbsolute) use ($mainShareId, $mainUrl, $thumbUrl): string {
        $this->assertFalse($isAbsolute);

        if ($share->aggregateRootId()->toString() === $mainShareId) {
          return $mainUrl;
        }

        return $thumbUrl;
      });

    $handler = $this->createHandler();
    $result = $handler(new GetExternalUploadResponseQuery($imageId, 'test.jpg', $userId));

    $this->assertCount(4, $invocations);
    $this->assertInstanceOf(CreateShareCommand::class, $invocations[0]);
    $this->assertInstanceOf(PublishShareCommand::class, $invocations[1]);
    $this->assertEquals($mainShareId, $invocations[1]->getShareId());
    $this->assertInstanceOf(CreateShareCommand::class, $invocations[2]);
    $this->assertInstanceOf(PublishShareCommand::class, $invocations[3]);
    $this->assertEquals($thumbShareId, $invocations[3]->getShareId());

    $this->assertSame($mainUrl, $result['url']);
    $this->assertSame($thumbUrl, $result['thumbnailUrl']);
    $this->assertSame($imageId, $result['id']);
  }

  #[Test]
  public function itDoesNotPublishWhenPreferenceIsFalse(): void {
    $imageId = Uuid::uuid4()->toString();
    $userId = Uuid::uuid4()->toString();
    $mainUrl = '/share/main';
    $thumbUrl = '/share/thumb';

    $this->preferencesRepository = $this->createStub(UserPreferencesRepositoryInterface::class);
    $this->preferencesRepository->method('findByUserId')->willReturn($this->createPreferencesView(false));

    $mainShare = $this->createShareStub(Uuid::uuid4()->toString(), false);
    $thumbShare = $this->createShareStub(Uuid::uuid4()->toString(), false);

    $invocations = [];
    $this->commandBus
      ->expects($this->exactly(2))
      ->method('handleSync')
      ->willReturnCallback(function (object $command) use (&$invocations, $mainShare, $thumbShare): mixed {
        $invocations[] = $command;
        $this->assertInstanceOf(CreateShareCommand::class, $command);

        if (count($invocations) === 1) {
          return CreateShareResult::created($mainShare);
        }

        return CreateShareResult::created($thumbShare);
      });

    $this->shareService
      ->expects($this->exactly(2))
      ->method('resolveUrl')
      ->willReturnOnConsecutiveCalls($mainUrl, $thumbUrl);

    $handler = $this->createHandler();
    $result = $handler(new GetExternalUploadResponseQuery($imageId, 'test.jpg', $userId));

    $this->assertCount(2, $invocations);
    $this->assertSame($mainUrl, $result['url']);
    $this->assertSame($thumbUrl, $result['thumbnailUrl']);
    $this->assertSame($imageId, $result['id']);
  }

  #[Test]
  public function itDoesNotPublishWhenPreferencesViewIsMissing(): void {
    $imageId = Uuid::uuid4()->toString();
    $userId = Uuid::uuid4()->toString();
    $mainUrl = '/share/main';
    $thumbUrl = '/share/thumb';

    $this->preferencesRepository = $this->createStub(UserPreferencesRepositoryInterface::class);
    $this->preferencesRepository->method('findByUserId')->willReturn(null);

    $mainShare = $this->createShareStub(Uuid::uuid4()->toString(), false);
    $thumbShare = $this->createShareStub(Uuid::uuid4()->toString(), false);

    $invocations = [];
    $this->commandBus
      ->expects($this->exactly(2))
      ->method('handleSync')
      ->willReturnCallback(function (object $command) use (&$invocations, $mainShare, $thumbShare): mixed {
        $invocations[] = $command;
        $this->assertInstanceOf(CreateShareCommand::class, $command);

        if (count($invocations) === 1) {
          return CreateShareResult::created($mainShare);
        }

        return CreateShareResult::created($thumbShare);
      });

    $this->shareService
      ->expects($this->exactly(2))
      ->method('resolveUrl')
      ->willReturnOnConsecutiveCalls($mainUrl, $thumbUrl);

    $handler = $this->createHandler();
    $result = $handler(new GetExternalUploadResponseQuery($imageId, 'test.jpg', $userId));

    $this->assertCount(2, $invocations);
    $this->assertSame($mainUrl, $result['url']);
    $this->assertSame($thumbUrl, $result['thumbnailUrl']);
    $this->assertSame($imageId, $result['id']);
  }

  #[Test]
  public function itDoesNotPublishWhenShareIsAlreadyPublished(): void {
    $imageId = Uuid::uuid4()->toString();
    $userId = Uuid::uuid4()->toString();
    $mainUrl = '/share/main';
    $thumbUrl = '/share/thumb';

    $this->preferencesRepository = $this->createStub(UserPreferencesRepositoryInterface::class);
    $this->preferencesRepository->method('findByUserId')->willReturn($this->createPreferencesView(true));

    $mainShare = $this->createShareStub(Uuid::uuid4()->toString(), true);
    $thumbShare = $this->createShareStub(Uuid::uuid4()->toString(), true);

    $invocations = [];
    $this->commandBus
      ->expects($this->exactly(2))
      ->method('handleSync')
      ->willReturnCallback(function (object $command) use (&$invocations, $mainShare, $thumbShare): mixed {
        $invocations[] = $command;
        $this->assertInstanceOf(CreateShareCommand::class, $command);

        if (count($invocations) === 1) {
          return CreateShareResult::created($mainShare);
        }

        return CreateShareResult::created($thumbShare);
      });

    $this->shareService
      ->expects($this->exactly(2))
      ->method('resolveUrl')
      ->willReturnOnConsecutiveCalls($mainUrl, $thumbUrl);

    $handler = $this->createHandler();
    $result = $handler(new GetExternalUploadResponseQuery($imageId, 'test.jpg', $userId));

    $this->assertCount(2, $invocations);
    $this->assertSame($mainUrl, $result['url']);
    $this->assertSame($thumbUrl, $result['thumbnailUrl']);
    $this->assertSame($imageId, $result['id']);
  }
}
