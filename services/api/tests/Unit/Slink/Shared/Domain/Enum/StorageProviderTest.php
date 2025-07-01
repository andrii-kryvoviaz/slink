<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Domain\Enum;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\Enum\StorageProvider;
use ValueError;

final class StorageProviderTest extends TestCase {

  #[Test]
  public function itCanBeCreatedFromString(): void {
    $this->assertEquals(StorageProvider::Local, StorageProvider::from('local'));
    $this->assertEquals(StorageProvider::SmbShare, StorageProvider::from('smb'));
    $this->assertEquals(StorageProvider::AmazonS3, StorageProvider::from('s3'));
  }

  #[Test]
  public function itCanTryFromString(): void {
    $this->assertEquals(StorageProvider::Local, StorageProvider::tryFrom('local'));
    $this->assertEquals(StorageProvider::SmbShare, StorageProvider::tryFrom('smb'));
    $this->assertEquals(StorageProvider::AmazonS3, StorageProvider::tryFrom('s3'));

    $result = StorageProvider::tryFrom('nonexistent_value');
    $this->assertEmpty($result);
  }

  #[Test]
  public function itComparesAllCombinations(): void {
    $providers = [
      StorageProvider::Local,
      StorageProvider::SmbShare,
      StorageProvider::AmazonS3
    ];

    foreach ($providers as $i => $provider1) {
      foreach ($providers as $j => $provider2) {
        if ($i === $j) {
          $this->assertTrue($provider1->equals($provider2));
        } else {
          $this->assertFalse($provider1->equals($provider2));
        }
      }
    }
  }

  #[Test]
  public function itComparesDifferentProviders(): void {
    $provider1 = StorageProvider::Local;
    $provider2 = StorageProvider::SmbShare;

    $this->assertFalse($provider1->equals($provider2));
  }

  #[Test]
  public function itComparesEqualProviders(): void {
    $provider1 = StorageProvider::Local;
    $provider2 = StorageProvider::Local;

    $this->assertTrue($provider1->equals($provider2));
  }

  #[Test]
  public function itHasAllExpectedCases(): void {
    $cases = StorageProvider::cases();

    $this->assertCount(3, $cases);
    $this->assertContains(StorageProvider::Local, $cases);
    $this->assertContains(StorageProvider::SmbShare, $cases);
    $this->assertContains(StorageProvider::AmazonS3, $cases);
  }

  #[Test]
  public function itHasCorrectValues(): void {
    $this->assertEquals('local', StorageProvider::Local->value);
    $this->assertEquals('smb', StorageProvider::SmbShare->value);
    $this->assertEquals('s3', StorageProvider::AmazonS3->value);
  }

  #[Test]
  public function itThrowsExceptionForInvalidValue(): void {
    $this->expectException(ValueError::class);

    StorageProvider::from('invalid');
  }
}
