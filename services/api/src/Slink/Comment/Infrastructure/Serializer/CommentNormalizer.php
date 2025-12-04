<?php

declare(strict_types=1);

namespace Slink\Comment\Infrastructure\Serializer;

use Slink\Comment\Domain\Service\CommentEditPolicy;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CommentNormalizer implements NormalizerInterface, NormalizerAwareInterface {
  use NormalizerAwareTrait;

  private const string ALREADY_CALLED_KEY = self::class . '_ALREADY_CALLED';

  /**
   * @param CommentView $data
   * @param array<string, mixed> $context
   * @return array<string, mixed>
   */
  public function normalize(mixed $data, ?string $format = null, array $context = []): array {
    $objectHash = spl_object_hash($data);
    $context[self::ALREADY_CALLED_KEY][$objectHash] = true;

    /** @var array<string, mixed> $normalized */
    $normalized = $this->normalizer->normalize($data, $format, $context);

    if (!$data->isDeleted()) {
      $normalized['canEdit'] = CommentEditPolicy::canEdit($data->getCreatedAt());
    } else {
      $normalized['canEdit'] = false;
    }

    return $normalized;
  }

  public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool {
    if (!$data instanceof CommentView) {
      return false;
    }

    $objectHash = spl_object_hash($data);
    return !isset($context[self::ALREADY_CALLED_KEY][$objectHash]);
  }

  /**
   * @return array<class-string, bool>
   */
  public function getSupportedTypes(?string $format): array {
    return [
      CommentView::class => false,
    ];
  }
}
