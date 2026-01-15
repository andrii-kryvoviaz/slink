<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Share\Application\Command\ShareImage\ShareImageCommand;
use Slink\Share\Application\Query\FindShareByTargetUrl\FindShareByTargetUrlQuery;
use Slink\Share\Application\Service\ShareService;
use Slink\Share\Domain\Enum\ShareType;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareResult;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Share\Infrastructure\ReadModel\View\ShortUrlView;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Application\Query\QueryBusInterface;

final class ShareServiceTest extends TestCase {
  private MockObject&CommandBusInterface $commandBus;
  private MockObject&QueryBusInterface $queryBus;
  private MockObject&ConfigurationProviderInterface $configurationProvider;
  private ShareService $shareService;

  protected function setUp(): void {
    $this->commandBus = $this->createMock(CommandBusInterface::class);
    $this->queryBus = $this->createMock(QueryBusInterface::class);
    $this->configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    
    $this->shareService = new ShareService(
      $this->commandBus,
      $this->queryBus,
      $this->configurationProvider
    );
  }

  #[Test]
  public function itReturnsSignedResultWhenShorteningIsDisabled(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $targetUrl = '/image/test.jpg';

    $this->configurationProvider
      ->expects($this->once())
      ->method('get')
      ->with('share.enableUrlShortening')
      ->willReturn(false);

    $this->queryBus
      ->expects($this->never())
      ->method('ask');

    $this->commandBus
      ->expects($this->never())
      ->method('handleSync');

    $result = $this->shareService->share($imageId, $targetUrl);

    $this->assertInstanceOf(ShareResult::class, $result);
    $this->assertEquals(ShareType::Signed, $result->getType());
    $this->assertEquals($targetUrl, $result->getTargetUrl());
    $this->assertNull($result->getShortCode());
  }

  #[Test]
  public function itReturnsExistingShortCodeWhenShareExists(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $targetUrl = '/image/test.jpg';
    $existingShortCode = 'aBcD1234';

    $this->configurationProvider
      ->expects($this->once())
      ->method('get')
      ->with('share.enableUrlShortening')
      ->willReturn(true);

    $shortUrlView = $this->createMock(ShortUrlView::class);
    $shortUrlView->method('getShortCode')->willReturn($existingShortCode);

    $shareView = $this->createMock(ShareView::class);
    $shareView->method('getShortUrl')->willReturn($shortUrlView);

    $this->queryBus
      ->expects($this->once())
      ->method('ask')
      ->with($this->isInstanceOf(FindShareByTargetUrlQuery::class))
      ->willReturn($shareView);

    $this->commandBus
      ->expects($this->never())
      ->method('handleSync');

    $result = $this->shareService->share($imageId, $targetUrl);

    $this->assertInstanceOf(ShareResult::class, $result);
    $this->assertEquals(ShareType::ShortUrl, $result->getType());
    $this->assertEquals($existingShortCode, $result->getShortCode());
  }

  #[Test]
  public function itCreatesNewShortUrlWhenShareDoesNotExist(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $targetUrl = '/image/test.jpg';
    $newShortCode = 'xYz98765';

    $this->configurationProvider
      ->expects($this->once())
      ->method('get')
      ->with('share.enableUrlShortening')
      ->willReturn(true);

    $this->queryBus
      ->expects($this->once())
      ->method('ask')
      ->with($this->isInstanceOf(FindShareByTargetUrlQuery::class))
      ->willReturn(null);

    $share = $this->createMock(Share::class);
    $share->method('getShortCode')->willReturn($newShortCode);

    $this->commandBus
      ->expects($this->once())
      ->method('handleSync')
      ->with($this->callback(function (ShareImageCommand $command) use ($imageId, $targetUrl) {
        return $command->getImageId() === $imageId
          && $command->getTargetUrl() === $targetUrl
          && $command->shouldCreateShortUrl() === true;
      }))
      ->willReturn($share);

    $result = $this->shareService->share($imageId, $targetUrl);

    $this->assertInstanceOf(ShareResult::class, $result);
    $this->assertEquals(ShareType::ShortUrl, $result->getType());
    $this->assertEquals($newShortCode, $result->getShortCode());
  }

  #[Test]
  public function itDefaultsToEnabledWhenConfigIsNull(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $targetUrl = '/image/test.jpg';

    $this->configurationProvider
      ->expects($this->once())
      ->method('get')
      ->with('share.enableUrlShortening')
      ->willReturn(null);

    $this->queryBus
      ->method('ask')
      ->willReturn(null);

    $share = $this->createMock(Share::class);
    $share->method('getShortCode')->willReturn('shortCode');

    $this->commandBus
      ->method('handleSync')
      ->willReturn($share);

    $result = $this->shareService->share($imageId, $targetUrl);

    $this->assertEquals(ShareType::ShortUrl, $result->getType());
  }
}
