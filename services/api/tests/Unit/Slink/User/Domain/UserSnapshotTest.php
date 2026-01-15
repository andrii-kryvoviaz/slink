<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Role;
use Slink\User\Domain\ValueObject\RoleSet;
use Slink\User\Domain\ValueObject\Username;

final class UserSnapshotTest extends TestCase {

  #[Test]
  public function itCanCreateSnapshot(): void {
    $userId = ID::generate();
    $email = Email::fromString('john@example.com');
    $username = Username::fromString('johndoe');
    $displayName = DisplayName::fromString('John Doe');
    $hashedPassword = HashedPassword::encode('password123');
    $roles = RoleSet::create([Role::fromString('ROLE_USER'), Role::fromString('ROLE_ADMIN')]);

    $user = $this->createUserWithState($userId, $email, $username, $displayName, $hashedPassword, UserStatus::Active, $roles);

    $reflection = new \ReflectionClass($user);
    $method = $reflection->getMethod('createSnapshotState');

    $snapshot = $method->invoke($user);

    $this->assertEquals('john@example.com', $snapshot['email']);
    $this->assertEquals('johndoe', $snapshot['username']);
    $this->assertEquals('John Doe', $snapshot['displayName']);
    $this->assertEquals(UserStatus::Active->value, $snapshot['status']);
    $this->assertContains('ROLE_USER', $snapshot['roles']);
    $this->assertContains('ROLE_ADMIN', $snapshot['roles']);
  }

  #[Test]
  public function itCanRestoreFromSnapshot(): void {
    $userId = ID::fromString('test-user-id');
    $snapshotData = [
      'email' => 'jane@example.com',
      'username' => 'janedoe',
      'displayName' => 'Jane Doe',
      'hashedPassword' => password_hash('secret123', PASSWORD_BCRYPT),
      'status' => UserStatus::Active->value,
      'roles' => ['ROLE_USER', 'ROLE_MODERATOR']
    ];

    $reflection = new \ReflectionClass(User::class);
    $method = $reflection->getMethod('reconstituteFromSnapshotState');

    $user = $method->invoke(null, $userId, $snapshotData);

    $this->assertEquals('test-user-id', $user->aggregateRootId()->toString());
    $this->assertEquals('janedoe', $user->getUsername()->toString());
    $this->assertEquals('Jane Doe', $user->getDisplayName()->toString());
    $this->assertEquals(UserStatus::Active, $user->getStatus());
    
    $userRoles = $user->getRoles();
    $this->assertContains('ROLE_USER', $userRoles);
    $this->assertContains('ROLE_MODERATOR', $userRoles);
  }

  #[Test]
  public function itMaintainsDataIntegrityThroughSnapshotCycle(): void {
    $userId = ID::generate();
    $email = Email::fromString('test@example.com');
    $username = Username::fromString('testuser');
    $displayName = DisplayName::fromString('Test User');
    $hashedPassword = HashedPassword::encode('testpass');
    $roles = RoleSet::create([Role::fromString('ROLE_USER')]);

    $originalUser = $this->createUserWithState($userId, $email, $username, $displayName, $hashedPassword, UserStatus::Active, $roles);

    $reflection = new \ReflectionClass($originalUser);
    $createMethod = $reflection->getMethod('createSnapshotState');

    $snapshotData = $createMethod->invoke($originalUser);

    $restoreMethod = $reflection->getMethod('reconstituteFromSnapshotState');

    $restoredUser = $restoreMethod->invoke(null, $userId, $snapshotData);

    $this->assertEquals($originalUser->aggregateRootId()->toString(), $restoredUser->aggregateRootId()->toString());
    $this->assertEquals($originalUser->getUsername()->toString(), $restoredUser->getUsername()->toString());
    $this->assertEquals($originalUser->getDisplayName()->toString(), $restoredUser->getDisplayName()->toString());
    $this->assertEquals($originalUser->getStatus(), $restoredUser->getStatus());
    $this->assertEquals($originalUser->getRoles(), $restoredUser->getRoles());
  }

  private function createUserWithState(
    ID $userId,
    Email $email,
    Username $username,
    DisplayName $displayName,
    HashedPassword $hashedPassword,
    UserStatus $status,
    RoleSet $roles
  ): User {
    $user = new \ReflectionClass(User::class);
    $instance = $user->newInstanceWithoutConstructor();
    
    $parentClass = $user->getParentClass();
    if ($parentClass === false) {
      throw new \RuntimeException('Unable to get parent class');
    }
    $aggregateRootIdProperty = $parentClass->getProperty('aggregateRootId');

    $aggregateRootIdProperty->setValue($instance, $userId);
    
    $emailProperty = $user->getProperty('email');

    $emailProperty->setValue($instance, $email);
    
    $usernameProperty = $user->getProperty('username');

    $usernameProperty->setValue($instance, $username);
    
    $displayNameProperty = $user->getProperty('displayName');

    $displayNameProperty->setValue($instance, $displayName);
    
    $hashedPasswordProperty = $user->getProperty('hashedPassword');

    $hashedPasswordProperty->setValue($instance, $hashedPassword);
    
    $statusProperty = $user->getProperty('status');

    $statusProperty->setValue($instance, $status);
    
    $rolesProperty = $user->getProperty('roles');

    $rolesProperty->setValue($instance, $roles);
    
    $preferencesProperty = $user->getProperty('preferences');
    $preferencesProperty->setValue($instance, \Slink\User\Domain\ValueObject\UserPreferences::empty());
    
    return $instance;
  }
}