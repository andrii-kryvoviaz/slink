<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Access;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class GuestUploadTest extends HttpTestCase {
  private function guestUpload(?string $token = null): int {
    $headers = [];

    if ($token !== null) {
      $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }

    $this->client->request('POST', '/api/guest/upload', [], ['image' => $this->sampleImage()], $headers);

    return $this->client->getResponse()->getStatusCode();
  }

  #[Test]
  public function anonymousGuestUploadIsForbiddenWhenDisabled(): void {
    $this->setAccessSettings(['allowGuestUploads' => false]);

    self::assertSame(403, $this->guestUpload());
  }

  #[Test]
  public function anonymousGuestUploadIsAllowedWhenEnabled(): void {
    $this->setAccessSettings(['allowGuestUploads' => true]);

    self::assertContains($this->guestUpload(), [200, 201]);
  }

  #[Test]
  public function authenticatedBearerIsIgnoredOnGuestRouteAndForbiddenWhenDisabled(): void {
    $this->setAccessSettings(['allowGuestUploads' => false]);

    $userId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    self::assertNotSame('', $userId);
    $token = $this->login('memberuser', self::PASSWORD);

    self::assertSame(403, $this->guestUpload($token));
  }

  #[Test]
  public function authenticatedGuestUploadIsAllowedWhenEnabled(): void {
    $this->setAccessSettings(['allowGuestUploads' => true]);

    $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $token = $this->login('memberuser', self::PASSWORD);

    self::assertContains($this->guestUpload($token), [200, 201]);
  }
}
