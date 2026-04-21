<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Resource;

use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Symfony\Component\Serializer\Attribute\Groups;

final class ShareListItem {
  /**
   * @param array{id: string, name: string, previewUrl: ?string} $shareableMeta
   */
  public function __construct(
    private readonly ShareView $view,
    private readonly string $url,
    private readonly array $shareableMeta,
  ) {}

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

  /** @var array{id: string, name: string, previewUrl: ?string} */
  #[Groups(['public'])]
  public array $shareable {
    get => $this->shareableMeta;
  }
}
