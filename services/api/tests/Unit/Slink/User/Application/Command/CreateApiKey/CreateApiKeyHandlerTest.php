<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\CreateApiKey;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Command\CreateApiKey\CreateApiKeyCommand;
use Slink\User\Application\Command\CreateApiKey\CreateApiKeyHandler;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\DisplayName;

final class CreateApiKeyHandlerTest extends TestCase {
  private UserStoreRepositoryInterface&MockObject $userRepository;
  private CreateApiKeyHandler $handler;

  protected function setUp(): void {
    $this->userRepository = $this->createMock(UserStoreRepositoryInterface::class);
    $this->handler = new CreateApiKeyHandler($this->userRepository);
  }

  public function testItCreatesApiKeySuccessfully(): void {
    $userId = ID::generate();
    $keyName = 'Test Key';
    $expiresAt = '2025-12-31 23:59:59';
    
    $command = new CreateApiKeyCommand($keyName, $expiresAt);
    
    $user = $this->createUser();
    
    $this->userRepository
      ->expects($this->once())
      ->method('get')
      ->with($userId)
      ->willReturn($user);
    
    $this->userRepository
      ->expects($this->once())
      ->method('store')
      ->with($user);
    
    $result = $this->handler->__invoke($command, $userId->toString());
    
    $this->assertStringStartsWith('sk_', $result);
  }

  public function testItCreatesApiKeyWithoutExpirationDate(): void {
    $userId = ID::generate();
    $keyName = 'Permanent Key';
    
    $command = new CreateApiKeyCommand($keyName);
    
    $user = $this->createUser();
    
    $this->userRepository
      ->expects($this->once())
      ->method('get')
      ->with($userId)
      ->willReturn($user);
    
    $this->userRepository
      ->expects($this->once())
      ->method('store')
      ->with($user);
    
    $result = $this->handler->__invoke($command, $userId->toString());
    
    $this->assertStringStartsWith('sk_', $result);
  }

  private function createUser(): User {
    $id = ID::generate();
    $credentials = Credentials::fromPlainCredentials('test@example.com', 'testuser', 'password123');
    $displayName = DisplayName::fromString('Test User');
    $status = UserStatus::Active;
    $context = $this->createUserCreationContext();

    $user = User::create($id, $credentials, $displayName, $status, $context);
    $user->releaseEvents();

    return $user;
  }

  private function createUserCreationContext(): UserCreationContext {
    $uniqueEmailSpec = $this->createMock(UniqueEmailSpecificationInterface::class);
    $uniqueEmailSpec->method('isUnique')->willReturn(true);

    $uniqueUsernameSpec = $this->createMock(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $uniqueDisplayNameSpec = $this->createMock(UniqueDisplayNameSpecificationInterface::class);
    $uniqueDisplayNameSpec->method('isUnique')->willReturn(true);

    return new UserCreationContext($uniqueEmailSpec, $uniqueUsernameSpec, $uniqueDisplayNameSpec);
  }
}
