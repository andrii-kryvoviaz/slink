<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Access;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Integration\Http\HttpTestCase;

final class GuestUploadTest extends HttpTestCase {
  private function upload(?string $token = null): Response {
    $headers = [];

    if ($token !== null) {
      $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }

    $this->client->request('POST', '/api/upload', [], ['image' => $this->sampleImage()], $headers);

    return $this->client->getResponse();
  }

  #[Test]
  public function anonymousUploadIsUnauthorizedWhenGuestUploadsDisabled(): void {
    $this->setAccessSettings(['allowGuestUploads' => false]);

    self::assertSame(401, $this->upload()->getStatusCode());
  }

  #[Test]
  public function anonymousUploadIsAllowedAsGuestWhenEnabled(): void {
    $this->setAccessSettings(['allowGuestUploads' => true]);

    $response = $this->upload();

    self::assertContains($response->getStatusCode(), [200, 201]);
    self::assertFalse($response->headers->has('Location'));
  }

  #[Test]
  public function authenticatedUploadIsAllowedEvenWhenGuestUploadsDisabled(): void {
    $this->setAccessSettings(['allowGuestUploads' => false]);

    $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $token = $this->login('memberuser', self::PASSWORD);

    $response = $this->upload($token);

    self::assertContains($response->getStatusCode(), [200, 201]);
    self::assertTrue($response->headers->has('Location'));
  }

  #[Test]
  public function authenticatedUploadIsAllowedWhenGuestUploadsEnabled(): void {
    $this->setAccessSettings(['allowGuestUploads' => true]);

    $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $token = $this->login('memberuser', self::PASSWORD);

    self::assertContains($this->upload($token)->getStatusCode(), [200, 201]);
  }
}
