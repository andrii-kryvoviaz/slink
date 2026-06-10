<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('slink.image.upload_stage')]
interface UploadStageInterface {
  public function process(UploadContext $context): UploadContext;
}
