<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Comment;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\Integration\Http\HttpTestCase;

final class CommentAccessTest extends HttpTestCase {
  private string $ownerToken = '';
  private string $nonOwnerToken = '';
  private string $shareViewerToken = '';

  private function bootActors(): void {
    $this->createUser('owner@local.test', 'owneruser', self::PASSWORD);
    $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $this->createUser('shareviewer@local.test', 'shareviewer', self::PASSWORD);

    $this->ownerToken = $this->login('owneruser', self::PASSWORD);
    $this->nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);
    $this->shareViewerToken = $this->login('shareviewer', self::PASSWORD);
  }

  private function commentsUrl(string $imageId): string {
    return \sprintf('/api/image/%s/comments', $imageId);
  }

  private function getComments(string $imageId, ?string $token = null): int {
    return $this->apiRequest('GET', $this->commentsUrl($imageId), $token);
  }

  private function attemptPostComment(string $imageId, ?string $token = null): int {
    return $this->apiRequest(
      'POST',
      $this->commentsUrl($imageId),
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['content' => 'integration comment'], JSON_THROW_ON_ERROR),
    );
  }

  /**
   * @return array{0: string, 1: string}
   */
  private function privateImageInPublishedCollection(): array {
    $imageId = $this->uploadImage($this->ownerToken, false);
    $collectionId = $this->createCollection($this->ownerToken);
    $this->addImageToCollection($this->ownerToken, $collectionId, $imageId);
    $shareId = $this->createCollectionShare($this->ownerToken, $collectionId);
    $this->publishShare($this->ownerToken, $shareId);

    return [$imageId, $shareId];
  }

  private function mercureCookie(): ?Cookie {
    foreach ($this->client->getResponse()->headers->getCookies() as $cookie) {
      if ($cookie->getName() === 'mercureAuthorization') {
        return $cookie;
      }
    }

    return null;
  }

  private function assertMercureCookiePresent(): void {
    $cookie = $this->mercureCookie();

    self::assertNotNull($cookie, 'Expected a mercureAuthorization cookie on granted comment reads.');
    self::assertSame('/sse', $cookie->getPath());
    self::assertNotEmpty((string) $cookie->getValue());
  }

  #[Test]
  public function ownerViewsCommentsOnOwnPrivateImage(): void {
    $this->setAccessSettings([]);
    $this->bootActors();
    $imageId = $this->uploadImage($this->ownerToken, false);

    self::assertSame(200, $this->getComments($imageId, $this->ownerToken));
    $this->assertMercureCookiePresent();
  }

  #[Test]
  public function nonOwnerViewsCommentsOnPublicImage(): void {
    $this->setAccessSettings([]);
    $this->bootActors();
    $imageId = $this->uploadImage($this->ownerToken, true);

    self::assertSame(200, $this->getComments($imageId, $this->nonOwnerToken));
  }

  #[Test]
  public function anonymousIsDeniedOnPublicImageWhenUnauthenticatedAccessIsOff(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => false]);
    $this->bootActors();
    $imageId = $this->uploadImage($this->ownerToken, true);

    self::assertSame(404, $this->getComments($imageId));
    self::assertNull($this->mercureCookie());
  }

  #[Test]
  public function nonOwnerIsDeniedOnPrivateImageWithoutCollection(): void {
    $this->setAccessSettings([]);
    $this->bootActors();
    $imageId = $this->uploadImage($this->ownerToken, false);

    self::assertSame(404, $this->getComments($imageId, $this->nonOwnerToken));
    self::assertNull($this->mercureCookie());
  }

  #[Test]
  public function nonOwnerIsDeniedOnPrivateImageInUnsharedCollection(): void {
    $this->setAccessSettings([]);
    $this->bootActors();
    $imageId = $this->uploadImage($this->ownerToken, false);
    $collectionId = $this->createCollection($this->ownerToken);
    $this->addImageToCollection($this->ownerToken, $collectionId, $imageId);

    self::assertSame(404, $this->getComments($imageId, $this->nonOwnerToken));
  }

  #[Test]
  public function nonOwnerViewsCommentsOnPrivateImageInPublishedCollection(): void {
    $this->setAccessSettings([]);
    $this->bootActors();
    [$imageId] = $this->privateImageInPublishedCollection();

    self::assertSame(200, $this->getComments($imageId, $this->nonOwnerToken));
    $this->assertMercureCookiePresent();
  }

  #[Test]
  public function anonymousViewsSharedCollectionCommentsWhenAuthNotRequired(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();
    [$imageId] = $this->privateImageInPublishedCollection();

    self::assertSame(200, $this->getComments($imageId));
    $this->assertMercureCookiePresent();
  }

  #[Test]
  public function anonymousIsDeniedSharedCollectionCommentsWhenAuthRequired(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => true]);
    $this->bootActors();
    [$imageId] = $this->privateImageInPublishedCollection();

    self::assertSame(404, $this->getComments($imageId));
    self::assertNull($this->mercureCookie());
  }

  #[Test]
  public function nonOwnerIsDeniedWhenCollectionShareIsUnpublished(): void {
    $this->setAccessSettings([]);
    $this->bootActors();
    [$imageId, $shareId] = $this->privateImageInPublishedCollection();
    $this->unpublishShare($this->ownerToken, $shareId);

    self::assertSame(404, $this->getComments($imageId, $this->nonOwnerToken));
    self::assertNull($this->mercureCookie());
  }

  #[Test]
  public function ownerCreatesCommentOnOwnPrivateImage(): void {
    $this->setAccessSettings([]);
    $this->bootActors();
    $imageId = $this->uploadImage($this->ownerToken, false);

    self::assertSame(201, $this->attemptPostComment($imageId, $this->ownerToken));
  }

  #[Test]
  public function nonOwnerCreatesCommentOnPublicImage(): void {
    $this->setAccessSettings([]);
    $this->bootActors();
    $imageId = $this->uploadImage($this->ownerToken, true);

    self::assertSame(201, $this->attemptPostComment($imageId, $this->nonOwnerToken));
  }

  #[Test]
  public function anonymousCannotCreateComment(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);
    $this->bootActors();
    $imageId = $this->uploadImage($this->ownerToken, true);

    self::assertSame(401, $this->attemptPostComment($imageId));
  }

  #[Test]
  public function nonOwnerCannotCreateCommentOnPrivateUnsharedImage(): void {
    $this->setAccessSettings([]);
    $this->bootActors();
    $imageId = $this->uploadImage($this->ownerToken, false);

    self::assertSame(404, $this->attemptPostComment($imageId, $this->nonOwnerToken));
  }

  #[Test]
  public function shareViewerCreatesCommentOnPrivateImageInPublishedCollection(): void {
    $this->setAccessSettings([]);
    $this->bootActors();
    [$imageId] = $this->privateImageInPublishedCollection();

    self::assertSame(201, $this->attemptPostComment($imageId, $this->shareViewerToken));
  }

  #[Test]
  public function logoutExpiresMercureCookie(): void {
    $this->setAccessSettings([]);
    $this->createUser('logout@local.test', 'logoutuser', self::PASSWORD);

    $this->client->request(
      'POST',
      '/api/auth/login',
      [],
      [],
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['username' => 'logoutuser', 'password' => self::PASSWORD], JSON_THROW_ON_ERROR),
    );

    /** @var array{access_token: string, refresh_token: string} $tokens */
    $tokens = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $status = $this->apiRequest(
      'POST',
      '/api/auth/logout',
      $tokens['access_token'],
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['refresh_token' => $tokens['refresh_token']], JSON_THROW_ON_ERROR),
    );

    self::assertContains($status, [200, 204]);

    $cookie = $this->mercureCookie();
    self::assertNotNull($cookie, 'Logout must send an expiring mercureAuthorization cookie.');
    self::assertSame('/sse', $cookie->getPath());
    self::assertLessThanOrEqual(\time(), $cookie->getExpiresTime());
  }
}
