<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload\Stage;

use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPhase;
use Slink\Image\Application\Service\Upload\UploadStageInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AsTaggedItem(priority: UploadPhase::Prepare->value)]
final readonly class AuthorizeCollectionsStage implements UploadStageInterface {
  public function __construct(
    private CollectionStoreRepositoryInterface $collectionStore,
    private AuthorizationCheckerInterface $access,
  ) {
  }

  public function process(UploadContext $context): UploadContext {
    if ($context->collectionIds() === [] || $context->userId() === null) {
      return $context;
    }

    foreach ($context->collectionIds() as $collectionId) {
      $collection = $this->collectionStore->get(ID::fromString($collectionId));

      if (!$this->access->isGranted(CollectionAccess::ManageItems, $collection)) {
        throw new CollectionAccessDeniedException();
      }
    }

    return $context;
  }
}
