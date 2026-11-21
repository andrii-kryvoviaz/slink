<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Share;

use Doctrine\ORM\EntityManagerInterface;

trait DrivesShareWrites {
  /**
   * @return iterable<string, array{string}>
   */
  public static function shareWrites(): iterable {
    yield 'publish' => ['publish'];
    yield 'unpublish' => ['unpublish'];
    yield 'set password' => ['password'];
    yield 'set expiration' => ['expiration'];
    yield 'unlock' => ['unlock'];
  }

  private function writeShare(string $write, string $shareId, string $ownerToken): int {
    $this->forgetLoadedRows();

    return match ($write) {
      'publish' => $this->apiRequest('PUT', \sprintf('/api/share/%s/publish', $shareId), $ownerToken),
      'unpublish' => $this->apiRequest('PUT', \sprintf('/api/share/%s/unpublish', $shareId), $ownerToken),
      'password' => $this->putJson(\sprintf('/api/share/%s/password', $shareId), $ownerToken, ['password' => 'AnotherPw123!']),
      'expiration' => $this->putJson(\sprintf('/api/share/%s/expiration', $shareId), $ownerToken, ['expiresAt' => $this->futureIso(7200)]),
      'unlock' => $this->unlockShare($shareId, self::SHARE_PASSWORD)[0],
      default => throw new \LogicException(\sprintf('Unknown share write "%s".', $write)),
    };
  }

  /**
   * @param array<string, mixed> $body
   */
  private function putJson(string $path, string $token, array $body): int {
    return $this->apiRequest(
      'PUT',
      $path,
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode($body, JSON_THROW_ON_ERROR),
    );
  }

  private function shareImage(string $imageId, string $ownerToken): string {
    $this->forgetLoadedRows();

    return $this->createImageShare($ownerToken, $imageId);
  }

  private function forgetLoadedRows(): void {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $entityManager->clear();
  }
}
