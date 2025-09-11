<?php

declare(strict_types=1);

namespace Unit\UI\Http\Rest\EventSubscriber;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Exception\DemoForbiddenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use UI\Http\Rest\EventSubscriber\DemoProtectionSubscriber;

final class DemoProtectionSubscriberTest extends TestCase {
  
  private MockObject&ConfigurationProviderInterface $configurationProvider;
  private DemoProtectionSubscriber $subscriber;

  protected function setUp(): void {
    $this->configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
    $this->subscriber = new DemoProtectionSubscriber($this->configurationProvider);
  }

  #[Test]
  public function itSubscribesToKernelRequestEvent(): void {
    $events = DemoProtectionSubscriber::getSubscribedEvents();
    
    $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
    $this->assertEquals(['onKernelRequest', 10], $events[KernelEvents::REQUEST]);
  }

  #[Test]
  public function itDoesNothingWhenDemoModeIsDisabled(): void {
    $this->configurationProvider
      ->expects($this->once())
      ->method('get')
      ->with('demo.enabled')
      ->willReturn(false);

    $request = new Request();
    $request->setMethod('POST');
    
    $event = $this->createRequestEvent($request);
    
    $this->subscriber->onKernelRequest($event);
  }

  /**
   * @param string $method
   */
  #[Test]
  #[DataProvider('forbiddenMethodsProvider')]
  public function itThrowsExceptionForForbiddenMethodsInDemoMode(string $method): void {
    $this->configurationProvider
      ->expects($this->once())
      ->method('get')
      ->with('demo.enabled')
      ->willReturn(true);

    $request = new Request();
    $request->setMethod($method);
    
    $event = $this->createRequestEvent($request);
    
    $this->expectException(DemoForbiddenException::class);
    $this->expectExceptionMessage('This action is not allowed in demo mode');
    
    $this->subscriber->onKernelRequest($event);
  }

  /**
   * @param string $method
   */
  #[Test]
  #[DataProvider('allowedMethodsProvider')]
  public function itAllowsGetAndOptionsMethodsInDemoMode(string $method): void {
    $this->configurationProvider
      ->expects($this->once())
      ->method('get')
      ->with('demo.enabled')
      ->willReturn(true);

    $request = new Request();
    $request->setMethod($method);
    
    $event = $this->createRequestEvent($request);
    
    $this->subscriber->onKernelRequest($event);
  }

  /**
   * @return array<int, array<int, string>>
   */
  public static function forbiddenMethodsProvider(): array {
    return [
      ['POST'],
      ['PUT'],
      ['PATCH'],
      ['DELETE'],
    ];
  }

  /**
   * @return array<int, array<int, string>>
   */
  public static function allowedMethodsProvider(): array {
    return [
      ['GET'],
      ['HEAD'],
      ['OPTIONS'],
    ];
  }

  private function createRequestEvent(Request $request): RequestEvent {
    $kernel = $this->createMock(HttpKernelInterface::class);
    return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
  }
}
