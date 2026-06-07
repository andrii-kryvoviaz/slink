<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ChunkedUpload;

use Slink\Image\Infrastructure\ChunkedUpload\Exception\ExpiredUploadTokenException;
use Slink\Image\Infrastructure\ChunkedUpload\Exception\InvalidUploadTokenException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class UploadTokenCodec {
  public const int TTL = 3600;

  public function __construct(
    #[Autowire('%kernel.secret%')]
    private string $secret,
  ) {
  }

  public function encode(UploadToken $token): string {
    $payload = $this->toJson($token->toPayload());
    $signature = $this->sign($payload);

    return $this->base64UrlEncode($payload) . '.' . $this->base64UrlEncode($signature);
  }

  /**
   * @throws InvalidUploadTokenException
   * @throws ExpiredUploadTokenException
   */
  public function decode(string $token, int $now): UploadToken {
    $segments = \explode('.', $token);

    if (\count($segments) !== 2) {
      throw new InvalidUploadTokenException('Malformed upload token.');
    }

    [$encodedPayload, $encodedSignature] = $segments;

    $payload = $this->base64UrlDecode($encodedPayload);
    $signature = $this->base64UrlDecode($encodedSignature);

    if ($payload === null || $signature === null) {
      throw new InvalidUploadTokenException('Malformed upload token.');
    }

    if (!\hash_equals($this->sign($payload), $signature)) {
      throw new InvalidUploadTokenException('Upload token signature mismatch.');
    }

    try {
      /** @var array<string, mixed> $data */
      $data = \json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException) {
      throw new InvalidUploadTokenException('Malformed upload token payload.');
    }

    $uploadToken = UploadToken::fromPayload($data);

    if ($uploadToken->isExpired($now)) {
      throw new ExpiredUploadTokenException();
    }

    return $uploadToken;
  }

  private function sign(string $payload): string {
    return \hash_hmac('sha256', $payload, $this->secret, true);
  }

  /**
   * @param array<string, mixed> $data
   */
  private function toJson(array $data): string {
    return \json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
  }

  private function base64UrlEncode(string $value): string {
    return \rtrim(\strtr(\base64_encode($value), '+/', '-_'), '=');
  }

  private function base64UrlDecode(string $value): ?string {
    $decoded = \base64_decode(\strtr($value, '-_', '+/'), true);

    return $decoded === false ? null : $decoded;
  }
}
