<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\User\Domain\ValueObject\ApiKey;

final class ApiKeyTest extends TestCase {
  public function testItGeneratesApiKeyWithName(): void {
    $name = 'Test API Key';
    
    $apiKey = ApiKey::generate($name);
    
    $this->assertEquals($name, $apiKey->getName());
    $this->assertStringStartsWith('sk_', $apiKey->getKey());
    $this->assertEquals(67, strlen($apiKey->getKey()));
    $this->assertInstanceOf(DateTime::class, $apiKey->getCreatedAt());
    $this->assertNull($apiKey->getExpiresAt());
    $this->assertFalse($apiKey->isExpired());
  }

  public function testItGeneratesApiKeyWithExpirationDate(): void {
    $name = 'Expiring API Key';
    $expiresAt = DateTime::fromString('2025-12-31 23:59:59');
    
    $apiKey = ApiKey::generate($name, $expiresAt);
    
    $this->assertEquals($name, $apiKey->getName());
    $this->assertEquals($expiresAt, $apiKey->getExpiresAt());
    $this->assertFalse($apiKey->isExpired());
  }

  public function testItCreatesFromExistingData(): void {
    $key = 'sk_existing_key_12345';
    $name = 'Existing Key';
    $createdAt = DateTime::fromString('2025-01-01 00:00:00');
    $expiresAt = DateTime::fromString('2025-12-31 23:59:59');
    
    $apiKey = ApiKey::fromExisting($key, $name, $createdAt, $expiresAt);
    
    $this->assertEquals($key, $apiKey->getKey());
    $this->assertEquals($name, $apiKey->getName());
    $this->assertEquals($createdAt, $apiKey->getCreatedAt());
    $this->assertEquals($expiresAt, $apiKey->getExpiresAt());
  }

  public function testItDetectsExpiredKey(): void {
    $name = 'Expired Key';
    $pastDate = DateTime::fromString('2020-01-01 00:00:00');
    
    $apiKey = ApiKey::generate($name, $pastDate);
    
    $this->assertTrue($apiKey->isExpired());
  }

  public function testItDetectsNonExpiredKey(): void {
    $name = 'Future Key';
    $futureDate = DateTime::fromString('2030-01-01 00:00:00');
    
    $apiKey = ApiKey::generate($name, $futureDate);
    
    $this->assertFalse($apiKey->isExpired());
  }

  public function testItReturnsKeyAsString(): void {
    $key = 'sk_test_key_67890';
    $name = 'Test Key';
    $createdAt = DateTime::now();
    
    $apiKey = ApiKey::fromExisting($key, $name, $createdAt);
    
    $this->assertEquals($key, $apiKey->toString());
  }

  public function testItHandlesDateTimeExceptionInIsExpired(): void {
    $name = 'Test Key';
    $invalidExpiresAt = $this->createMock(DateTime::class);
    $invalidExpiresAt->method('isBefore')->willThrowException(new DateTimeException(new \Exception('Invalid date')));
    
    $apiKey = ApiKey::fromExisting('sk_test', $name, DateTime::now(), $invalidExpiresAt);
    
    $this->assertTrue($apiKey->isExpired());
  }
}
