<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload\Stage;

use Slink\Image\Application\Service\CollectionMembershipAssigner;
use Slink\Image\Application\Service\ImageTagAssigner;
use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPhase;
use Slink\Image\Application\Service\Upload\UploadStageInterface;
use Slink\Image\Domain\Context\ImageCreationContext;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: UploadPhase::Persist->value)]
final readonly class CommitUploadStage implements UploadStageInterface {
  public function __construct(
    private ImageCreationContext $creationContext,
    private ImageTagAssigner $tagAssigner,
    private StorageInterface $storage,
    private ImageStoreRepositoryInterface $imageRepository,
    private CollectionMembershipAssigner $collectionAssigner,
  ) {
  }

  public function process(UploadContext $context): UploadContext {
    $image = Image::create(
      context: $this->creationContext,
      id: $context->id(),
      userId: $context->userId(),
      attributes: $context->attributes(),
      metadata: $context->metadata(),
      imageFile: $context->mediaFile(),
      license: $context->license(),
    );

    $this->tagAssigner->assign($image, $context->tagIds(), $context->userId());

    $this->storage->upload($context->file(), $context->fileName());

    $this->imageRepository->store($image);

    $this->collectionAssigner->assign($context->id(), $context->collectionIds(), $context->userId());

    return $context;
  }
}
