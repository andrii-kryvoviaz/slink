<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Serializer;

use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DateTimeNormalizer implements NormalizerInterface {
  /**
   * @param DateTime $data
   * @param array<string, mixed> $context
   * @return array{formattedDate: string, timestamp: int}
   */
  public function normalize(mixed $data, ?string $format = null, array $context = []): array {
    return [
      'formattedDate' => $data->getDateString(),
      'timestamp' => $data->getUnixTimeStamp(),
    ];
  }

  public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool {
    return $data instanceof DateTime;
  }

  /**
   * @return array<class-string, bool>
   */
  public function getSupportedTypes(?string $format): array {
    return [
      DateTime::class => true,
    ];
  }
}
