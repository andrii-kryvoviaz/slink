<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Resource;

use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Symfony\Component\Serializer\Attribute\Groups;

final class ShareListItem {
  /**
   * @var array{width: ?int, height: ?int, filter: ?string, format: ?string}
   */
  private readonly array $parsedTarget;

  /**
   * @param array{id: string, name: string, previewUrl: ?string, width?: int, height?: int, format?: string} $shareableMeta
   */
  public function __construct(
    private readonly ShareView $view,
    private readonly string $url,
    private readonly array $shareableMeta,
  ) {
    $this->parsedTarget = self::parseTargetPath($this->view->getTargetPath());
  }

  #[Groups(['public'])]
  public string $shareId {
    get => $this->view->getId();
  }

  #[Groups(['public'])]
  public string $shareUrl {
    get => $this->url;
  }

  #[Groups(['public'])]
  public ?string $shortCode {
    get => $this->view->getShortCode();
  }

  #[Groups(['public'])]
  public ShareableType $type {
    get => $this->view->getShareable()->getShareableType();
  }

  #[Groups(['public'])]
  public bool $isPublished {
    get => $this->view->isPublished();
  }

  #[Groups(['public'])]
  public ?string $expiresAt {
    get => $this->view->getExpiresAt()?->toString();
  }

  #[Groups(['public'])]
  public bool $isExpired {
    get {
      $expires = $this->view->getExpiresAt();
      return $expires !== null && $expires->isBeforeEquals(DateTime::now());
    }
  }

  #[Groups(['public'])]
  public bool $requiresPassword {
    get => $this->view->getPassword() !== null;
  }

  #[Groups(['public'])]
  public string $createdAt {
    get => $this->view->getCreatedAt()->toString();
  }

  /** @var array{id: string, name: string, previewUrl: ?string, width?: int, height?: int, format?: string} */
  #[Groups(['public'])]
  public array $shareable {
    get => $this->shareableMeta;
  }

  /** @var array{width: ?int, height: ?int, filter: ?string, format: ?string} */
  #[Groups(['public'])]
  public array $variant {
    get => [
      'width' => $this->parsedTarget['width'] ?? $this->shareableMeta['width'] ?? null,
      'height' => $this->parsedTarget['height'] ?? $this->shareableMeta['height'] ?? null,
      'filter' => $this->parsedTarget['filter'],
      'format' => $this->parsedTarget['format'] ?? $this->shareableMeta['format'] ?? null,
    ];
  }

  /**
   * @return array{width: ?int, height: ?int, filter: ?string, format: ?string}
   */
  private static function parseTargetPath(string $path): array {
    $parts = parse_url($path);
    $pathOnly = $parts['path'] ?? '';
    $query = [];

    if (isset($parts['query'])) {
      parse_str($parts['query'], $query);
    }

    $format = null;
    if (preg_match('/\.([a-zA-Z0-9]+)$/', $pathOnly, $match) === 1) {
      $format = strtolower($match[1]);
    }

    $filter = isset($query['filter']) && is_string($query['filter']) ? $query['filter'] : null;
    if ($filter === 'none' || $filter === '') {
      $filter = null;
    }

    return [
      'width' => isset($query['width']) ? (int) $query['width'] : null,
      'height' => isset($query['height']) ? (int) $query['height'] : null,
      'filter' => $filter,
      'format' => $format,
    ];
  }
}
