<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\RevokeApiKey;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Command\RevokeApiKey\RevokeApiKeyCommand;
use Slink\User\Application\Command\RevokeApiKey\RevokeApiKeyHandler;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;

final class RevokeApiKeyHandlerTest extends TestCase {
  private UserStoreRepositoryInterface&MockObject $userRepository;
  private RevokeApiKeyHandler $handler;

  protected function setUp(): void {
    $this->userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $this->handler = new RevokeApiKeyHandler($this->userRepository);
  }

  public function testItRevokesApiKeySuccessfully(): void {
    $userId = ID::generate()->toString();
    $keyId = 'key-123';
    
    $command = new RevokeApiKeyCommand($keyId);
    
    $user = $this->createMock(User::class);
    
    $this->userRepository
      ->expects($this->once())
      ->method('get')
      ->with(ID::fromString($userId))
      ->willReturn($user);
    
    $user
      ->expects($this->once())
      ->method('revokeApiKey')
      ->with($keyId);
    
    $this->userRepository
      ->expects($this->once())
      ->method('store')
      ->with($user);
    
    $this->handler->__invoke($command, $userId);
  }
}
