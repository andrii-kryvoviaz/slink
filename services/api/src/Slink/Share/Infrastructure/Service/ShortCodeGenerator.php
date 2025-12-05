<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Service;

use Slink\Share\Domain\Repository\ShortUrlRepositoryInterface;
use Slink\Share\Domain\Service\ShortCodeGeneratorInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(ShortCodeGeneratorInterface::class)]
final readonly class ShortCodeGenerator implements ShortCodeGeneratorInterface {
  private const string ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
  private const int CODE_LENGTH = 8;
  private const int MAX_ATTEMPTS = 10;

  public function __construct(
    private ShortUrlRepositoryInterface $shortUrlRepository,
  ) {
  }

  public function generate(): string {
    $attempts = 0;

    do {
      $code = $this->generateRandomCode();
      $attempts++;

      if ($attempts > self::MAX_ATTEMPTS) {
        throw new \RuntimeException('Failed to generate unique short code after ' . self::MAX_ATTEMPTS . ' attempts');
      }
    } while ($this->shortUrlRepository->existsByShortCode($code));

    return $code;
  }

  private function generateRandomCode(): string {
    $alphabetLength = strlen(self::ALPHABET);
    $code = '';

    for ($i = 0; $i < self::CODE_LENGTH; $i++) {
      $code .= self::ALPHABET[random_int(0, $alphabetLength - 1)];
    }

    return $code;
  }
}
