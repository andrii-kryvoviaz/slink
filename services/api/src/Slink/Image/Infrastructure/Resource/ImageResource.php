<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Resource;

use Slink\Image\Domain\Enum\License;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Infrastructure\Resource\ResourceData;
use Slink\Shared\Infrastructure\Resource\ResourceInterface;
use Slink\User\Domain\ValueObject\GuestUser;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Component\Serializer\Attribute\Groups;

final class ImageResource implements ResourceInterface {
  public function __construct(
    private readonly ImageView    $image,
    private readonly ResourceData $data = new ResourceData(),
  ) {
  }

  public function getType(): string {
    return ImageView::class;
  }

  #[Groups(['public'])]
  public string $id {
    get => $this->image->getUuid();
  }

  #[Groups(['public'])]
  public UserView|GuestUser $owner {
    get => $this->image->getOwner();
  }

  /**
   * @var array{
   *   fileName: string,
   *   description: string,
   *   isPublic: bool,
   *   createdAt: DateTime,
   *   views: int|null
   * }
   */
  #[Groups(['public'])]
  public array $attributes {
    get => [
      'fileName' => $this->image->getAttributes()->getFileName(),
      'description' => $this->image->getAttributes()->getDescription(),
      'isPublic' => $this->image->getAttributes()->isPublic(),
      'createdAt' => $this->image->getAttributes()->getCreatedAt(),
      'views' => $this->image->getAttributes()->getViews(),
    ];
  }

  /**
   * @var array{
   *   size: int,
   *   mimeType: string,
   *   width: int,
   *   height: int
   * }|null
   */
  #[Groups(['public'])]
  public ?array $metadata {
    get {
      $metadata = $this->image->getMetadata();
      if ($metadata === null) {
        return null;
      }

      return [
        'size' => $metadata->getSize(),
        'mimeType' => $metadata->getMimeType(),
        'width' => $metadata->getWidth(),
        'height' => $metadata->getHeight(),
      ];
    }
  }

  /** @var array<string, mixed> */
  #[Groups(['license'])]
  public array $license {
    get => ($this->image->getLicense() ?? License::AllRightsReserved)->toArray();
  }

  #[Groups(['bookmark'])]
  public int $bookmarkCount {
    get => $this->image->getBookmarkCount();
  }

  #[Groups(['bookmark'])]
  public bool $isBookmarked {
    get => $this->data->has('bookmarks', $this->image->getUuid());
  }

  /** @var array<string> */
  #[Groups(['collection'])]
  public array $collectionIds {
    get => $this->data->get('collections', $this->image->getUuid(), []);
  }

  /** @var array<string> */
  #[Groups(['tag'])]
  public array $tags {
    get => $this->image->getTags()->toArray();
  }
}
