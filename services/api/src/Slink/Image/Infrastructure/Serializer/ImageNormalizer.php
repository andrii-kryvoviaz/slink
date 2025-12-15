<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Serializer;

use Slink\Image\Domain\Enum\License;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ImageNormalizer implements NormalizerInterface, NormalizerAwareInterface {
  use NormalizerAwareTrait;

  private const string ALREADY_CALLED_KEY = self::class . '_ALREADY_CALLED';

  /**
   * @param array<string, mixed> $context
   * @return array<string, mixed>
   */
  public function normalize(mixed $data, ?string $format = null, array $context = []): array {
    $objectHash = spl_object_hash($data);
    $context[self::ALREADY_CALLED_KEY][$objectHash] = true;

    $attributes = $data->getAttributes();
    $metadata = $data->getMetadata();

    return [
      'id' => $data->getUuid(),
      'owner' => $this->normalizer->normalize($data->getOwner(), $format, $context),
      'attributes' => [
        'fileName' => $attributes->getFileName(),
        'description' => $attributes->getDescription(),
        'isPublic' => $attributes->isPublic(),
        'createdAt' => $this->normalizer->normalize($attributes->getCreatedAt(), $format, $context),
        'views' => $attributes->getViews(),
      ],
      'metadata' => $metadata ? [
        'size' => $metadata->getSize(),
        'mimeType' => $metadata->getMimeType(),
        'width' => $metadata->getWidth(),
        'height' => $metadata->getHeight(),
      ] : null,
      'license' => ($data->getLicense() ?? License::AllRightsReserved)->toArray(),
      'bookmarkCount' => $data->getBookmarkCount(),
      'isBookmarked' => $context['isBookmarked'] ?? false,
      'tags' => $this->normalizer->normalize($data->getTags()->toArray(), $format, $context),
    ];
  }

  public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool {
    if (!$data instanceof ImageView) {
      return false;
    }

    $objectHash = spl_object_hash($data);
    return !isset($context[self::ALREADY_CALLED_KEY][$objectHash]);
  }

  public function getSupportedTypes(?string $format): array {
    return [
      ImageView::class => false,
    ];
  }
}
